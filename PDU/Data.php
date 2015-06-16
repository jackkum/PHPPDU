<?php

/* 
 * Copyright (C) 2014 jackkum
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace jackkum\PHPPDU\PDU;

use jackkum\PHPPDU\PDU;

class Data {
	
	const HEADER_SIZE = 8;
	
	/**
	 * data length
	 * @var integer
	 */
	protected $_size;
	
	/**
	 * text message
	 * @var string
	 */
	protected $_data;
	
	/**
	 * parts sms
	 * @var array
	 */
	protected $_parts = array();
	
	/**
	 * text message is unicode
	 * @var boolean
	 */
	protected $_isUnicode = FALSE;
	
	/**
	 * message object
	 * @var PDU
	 */
	protected $_pdu;
	
	/**
	 * create object data
	 * @param PDU $pdu
	 */
	public function __construct(PDU $pdu)
	{
		// set encoding
		mb_internal_encoding('UTF-8');
		// set message
		$this->_pdu = $pdu;
	}
	
	/**
	 * parse pdu string
	 * @param PDU $pdu
	 * @return \self
	 */
	public static function parse(PDU $pdu)
	{
		$self = new Data($pdu);
		
		if($pdu->getDcs()->getTextAlphabet() == DCS::ALPHABET_UCS2){
			$self->_isUnicode = TRUE;
		}
		
		list($self->_data, $self->_size, $part) = Data\Part::parse($self);
		$self->_parts[] = $part;
		
		return $self;
	}
	
	/**
	 * merge parts
	 * @param PDU $pdu
	 */
	public function append(PDU $pdu)
	{
		$parts = $pdu->getParts();
		
		foreach($parts as $part){
			
			if( ! $this->_partExists($part)){
				$this->_parts[] = $part;
			}
		}
		
		$this->_sortParts();
	}
	
	/**
	 * check exists new part
	 * @param Data\Part $part
	 * @throws Exception if not equals pointers
	 * @return boolean
	 */
	protected function _partExists(Data\Part $part)
	{
		foreach($this->_parts as $_part){
			
			if($part->getHeader()->getPointer() != $_part->getHeader()->getPointer()){
				throw new Exception("Part from different message");
			}
			
			if($_part->getHeader()->getCurrent() == $part->getHeader()->getCurrent()){
				return TRUE;
			}
		}
		
		return FALSE;
	}

	/**
	 * sorting parts
	 */
	protected function _sortParts()
	{
		usort($this->_parts, function(Data\Part $part1, Data\Part $part2){
			$index1 = $part1->getHeader()->getCurrent();
			$index2 = $part2->getHeader()->getCurrent();
			
			return $index1 > $index2 ? 1 : -1;
		});
		
		$message = NULL;
		
		foreach($this->_parts as $part){
			$message .= $part->getText();
		}
		
		$this->_data = $message;
	}

	/**
	 * set text message
	 * @param string $data
	 */
	public function setData($data)
	{
		$this->_data = $data;
		
		// encode message
		$this->_checkData();
		
		// preapre parts
		$this->_prepareParts();
	}
	
	/**
	 * check message
	 */
	protected function _checkData()
	{
		// set is unicode to false
		$this->_isUnicode = FALSE;
		// set zero size
		$this->_size = 0;
		
		// check message
		for($i = 0; $i < mb_strlen($this->_data, 'UTF-8'); $i++){
			// get byte
			$byte = Helper::order(mb_substr($this->_data, $i, 1, 'UTF-8'));
			
			if($byte > 0xC0){
				$this->_isUnicode = TRUE;
			}
			
			$this->_size++;
		}
		
	}
	
	/**
	 * prepare parts of message
	 * @throws Exception
	 */
	protected function _prepareParts()
	{
		$max = 140;
		if($this->_isUnicode){
			// max length sms to unicode
			$max = 70;
			// can't compress message
			$this->getPdu()
				 ->getDcs()
				 ->setTextCompressed(FALSE)				// no compress
				 ->setTextAlphabet(DCS::ALPHABET_UCS2);	// type alphabet is UCS2
		}
		
		// if message is compressed
		if($this->getPdu()->getDcs()->getTextCompressed()){
			$max = 160;
		}
		
		$parts  = $this->_splitMessage($max);
		$header = (count($parts) > 1);
		$uniqid = rand(0, 65535);
		
		// message will be splited, need headers
		if($header){
			$this->getPdu()->getType()->setUdhi(1);
		}
		
		foreach($parts as $index => $text){
			$params = 
			($header ? 
				array(
					'SEGMENTS' => count($parts),
					'CURRENT'  => ($index+1),
					'POINTER'  => $uniqid
				) : 
				NULL
			);
			
			$part = NULL; 
			$size = 0;
			switch($this->getPdu()->getDcs()->getTextAlphabet()){
				
				case DCS::ALPHABET_DEFAULT:
					PDU::debug("Helper::encode7bit()");
					list($size,$part) = Helper::encode7bit($text);
					break;
				
				case DCS::ALPHABET_8BIT:
					PDU::debug("Helper::encode8BitMessage()");
					list($size,$part) = Helper::encode8Bit($text);
					break;
				
				case DCS::ALPHABET_UCS2:
					PDU::debug("Helper::encode16BitMessage()");
					list($size,$part) = Helper::encode16Bit($text);
					break;
				
				default:
					throw new Exception("Unknown alphabet");
			}

			if($header){
				$size += self::HEADER_SIZE;
			}
			
			$this->_parts[] = new Data\Part(
				$this,
				$part,
				$size,
				$params
			);
		}
		
	}
	
	/**
	 * split message
	 * @param integer $max
	 * @return array
	 */
	protected function _splitMessage($max)
	{
		
		// size less or equal max
		if($this->getSize() <= $max){
			return array($this->_data);
		}
		
		// parts of message
		$data   = array();
		$offset = 0;
		$size   = $max - self::HEADER_SIZE;
		
		while(TRUE)
		{
			$part    = mb_substr($this->_data, $offset, $size, 'UTF-8');
			PDU::debug("Part[" . $size . "]: " . $part);
			$data[]  = $part;
			$offset += $size;
			
			if($offset >= $this->getSize()){
				break;
			}
			
		}
		
		return $data;
	}
	
	
	/**
	 * getter text message
	 * @return string
	 */
	public function getData()
	{
		return $this->_data;
	}
	
	/**
	 * getter pdu
	 * @return PDU
	 */
	public function getPdu()
	{
		return $this->_pdu;
	}
	
	/**
	 * getter data size
	 * @return integer
	 */
	public function getSize()
	{
		return (int) $this->_size;
	}
	
	/**
	 * get message parts
	 * @return array
	 */
	public function getParts()
	{
		return $this->_parts;
	}
	
}