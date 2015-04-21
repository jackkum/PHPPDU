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

namespace jackkum\PHPPDU\PDU\Data;

class Part {
	
	/**
	 * header message
	 * @var \Header
	 */
	protected $_header;
	
	/**
	 * data in pdu format
	 * @var string
	 */
	protected $_data;
	
	/**
	 * size this part
	 * @var integer
	 */
	protected $_size;
	
	/**
	 * pdu data
	 * @var \Data
	 */
	protected $_parent;
	
	/**
	 * create data part
	 * @param string $data
	 * @param array|NULL $header
	 */
	public function __construct(\jackkum\PHPPDU\PDU\Data $parent, $data, $size, $header = NULL)
	{
		// parent 
		$this->_parent = $parent;
		
		// encoded string
		$this->_data = $data;
		
		// size this part
		$this->_size = $size;
		
		// have params for header
		if(is_array($header)){
			// create header
			$this->_header = new Header($header);
		}
	}
	
	/**
	 * parse pdu string
	 * @param \Data $data
	 * @return array [decded text, text size, self object]
	 * @throws Exception
	 */
	public static function parse(\jackkum\PHPPDU\PDU\Data $data)
	{
		$alphabet = $data->getPdu()->getDcs()->getTextAlphabet();
		$header   = NULL;
		$length   = $data->getPdu()->getUdl() * ($alphabet == \jackkum\PHPPDU\PDU\DCS::ALPHABET_UCS2 ? 4 : 2);
		
		if($data->getPdu()->getType()->getUdhi()){
			$header = Header::parse();
		}
		
		$hex = \jackkum\PHPPDU\PDU::getPduSubstr($length);
		
		switch($alphabet){
			case \jackkum\PHPPDU\PDU\DCS::ALPHABET_DEFAULT:
				\jackkum\PHPPDU\PDU::debug("Helper::decode7bit()");
				$text = \jackkum\PHPPDU\PDU\Helper::decode7bit($hex);
				break;
			
			case \jackkum\PHPPDU\PDU\DCS::ALPHABET_8BIT:
				\jackkum\PHPPDU\PDU::debug("Helper::decode8bit()");
				$text = \jackkum\PHPPDU\PDU\Helper::decode8bit($hex);
				break;
			
			case \jackkum\PHPPDU\PDU\DCS::ALPHABET_UCS2:
				\jackkum\PHPPDU\PDU::debug("Helper::decode16Bit()");
				$text = \jackkum\PHPPDU\PDU\Helper::decode16Bit($hex);
				break;
			
			default:
				throw new Exception("Unknown alpabet");
		}
		
		$size = mb_strlen($text);
		$self = new self(
			$data, 
			$hex, 
			$size, 
			($header ? $header->toArray() : NULL)
		);
		
		return array(
			$text,
			$size,
			$self
		);
	}


	/**
	 * getter data
	 * @return string
	 */
	public function getData()
	{
		return $this->_data;
	}
	
	/**
	 * getter header
	 * @return Header
	 */
	public function getHeader()
	{
		return $this->_header;
	}
	
	/**
	 * getter parent of part
	 * @return \Data
	 */
	public function getParent()
	{
		return $this->_parent;
	}
	
	/**
	 * getter size
	 * @return integer
	 */
	public function getSize()
	{
		return $this->_size;
	}
	
	/**
	 * convert pdu to srting
	 * @return string
	 */
	protected function _getPduString()
	{
		return (string) $this->_parent->getPdu();
	}
	
	/**
	 * to hex
	 * @return string
	 */
	protected function _getPartSize()
	{
		return sprintf("%02X", $this->_size);
	}
	
	/**
	 * magic method for cast part to string
	 * @return string
	 */
	public function __toString()
	{
		
		\jackkum\PHPPDU\PDU::debug("_getPduString() " . $this->_getPduString());
		\jackkum\PHPPDU\PDU::debug("_getPartSize() " . $this->_getPartSize());
		\jackkum\PHPPDU\PDU::debug("getHeader() " . $this->getHeader());
		\jackkum\PHPPDU\PDU::debug("getData() " . $this->getData());
		
		// concate pdu, size of part, headers, data
		return $this->_getPduString() . 
			   $this->_getPartSize()  . 
			   $this->getHeader()     . 
			   $this->getData();
	}
	
}