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

class DCS {
	
	/**
	 * GSM 03.38 V7.0.0 (1998-07).
	 */
	
	const CLASS_NONE                 = 0x00;
	const CLASS_MOBILE_EQUIPMENT     = 0x01;
	const CLASS_SIM_SPECIFIC_MESSAGE = 0x02;
	const CLASS_TERMINAL_EQUIPMENT   = 0x03;
	
	const INDICATION_TYPE_VOICEMAIL  = 0x00;
	const INDICATION_TYPE_FAX        = 0x01;
	const INDICATION_TYPE_EMAIL      = 0x02;
	const INDICATION_TYPE_OTHER      = 0x03;
	
	const ALPHABET_DEFAULT           = 0x00;
	const ALPHABET_8BIT              = 0x01;
	const ALPHABET_UCS2              = 0x02; // 16 bit unicode
	const ALPHABET_RESERVED          = 0x03;
	
	/**
	 * type encoding group
	 * @var integer
	 */
	protected $_encodeGroup = 0x00;
	
	/**
	 * specific data for encoding
	 * @var integer
	 */
	protected $_dataEncoding = 0x00;
	
	/**
	 * is compressed text
	 * @var boolean
	 */
	protected $_compressedText = FALSE;
	
	/**
	 * Text alphabet
	 * @var integer
	 */
	protected $_alphabet = self::ALPHABET_DEFAULT;
	
	/**
	 * use message class
	 * @var boolean
	 */
	protected $_useMessageClass = FALSE;
	
	/**
	 * current class message
	 * @var integer
	 */
	protected $_classMessage = self::CLASS_NONE;
	
	/**
	 * Discard Message
	 * @var boolean
	 */
	protected $_discardMessage = FALSE;
	
	/**
	 * Store Message
	 * @var boolean
	 */
	protected $_storeMessage = FALSE;
	
	/**
	 * Store Message UCS2
	 * @var boolean
	 */
	protected $_storeMessageUCS2 = FALSE;
	
	/**
	 * set 4-7 bits to 1 why for this, dont know
	 * @var boolean
	 */
	protected $_dataCodingAndMessageClass = FALSE;
	
	/**
	 * Message indication
	 * @var integer
	 */
	protected $_messageIndication = FALSE;
	
	/**
	 * set message type
	 * @var integer
	 */
	protected $_messageIndicationType = FALSE;

	/**
	 * parse pdu string
	 * @return \self
	 */
	public static function parse()
	{
		$DCS  = new self();
		$byte = hexdec(PDU::getPduSubstr(2));
		
		$DCS->_encodeGroup  = 0x0F&($byte>>4);
		$DCS->_dataEncoding = 0x0F&$byte;
		
		$DCS->_alphabet     = (3 & ($DCS->_dataEncoding>>2));
		$DCS->_classMessage = (3 & $DCS->_dataEncoding);
		
		switch($DCS->_encodeGroup){
			case 0x0C: $DCS->_discardMessage            = TRUE; break;
			case 0x0D: $DCS->_storeMessage              = TRUE; break;
			case 0x0E: $DCS->_storeMessageUCS2          = TRUE; break;
			case 0x0F: 
				$DCS->_dataCodingAndMessageClass = TRUE; 
				
				if($DCS->_dataEncoding & (1<<2)){
					$DCS->_alphabet = self::ALPHABET_8BIT;
				}
				
				break;
			
			default:
				
				if($DCS->_encodeGroup & (1<<4)){
					$DCS->_useMessageClass = TRUE;
				}
				
				if($DCS->_encodeGroup & (1<<5)){
					$DCS->_compressedText = TRUE;
				}
		}
		
		if($DCS->_discardMessage || $DCS->_storeMessage || $DCS->_storeMessageUCS2){
			
			if($DCS->_dataEncoding & (1<<3)){
				$DCS->_messageIndication     = TRUE;
				$DCS->_messageIndicationType = (3 & $DCS->_dataEncoding);
			}
			
		}
		
		return $DCS;
	}

	/**
	 * getter byte value
	 * @return integer
	 */
	public function getValue()
	{
		$this->_encodeGroup = 0x00;
		
		// set data encoding, from alphabet and message class
		$this->_dataEncoding = ($this->_alphabet<<2)|($this->_classMessage);
		
		// set message class bit
		if($this->_useMessageClass){
			$this->_encodeGroup |= (1<<4);
		} else {
			$this->_encodeGroup &= ~(1<<4);
		}
		
		// set is compressed bit
		if($this->_compressedText){
			$this->_encodeGroup |= (1<<5);
		} else {
			$this->_encodeGroup &= ~(1<<5);
		}
		
		// change encoding format
		if($this->_discardMessage || $this->_storeMessage || $this->_storeMessageUCS2){
			$this->_dataEncoding = 0x00;
			
			// set indication
			if($this->_messageIndication){
				$this->_dataEncoding |= (1<<3);
				
				// set message indication type
				$this->_dataEncoding |= $this->_messageIndicationType;
			}
			
		}
		
		// Discard Message
		if($this->_discardMessage){
			$this->_encodeGroup = 0x0C;
		}
		
		// Store Message
		if($this->_storeMessage){
			$this->_encodeGroup = 0x0D;
		}
		
		// Store Message UCS2
		if($this->_storeMessageUCS2){
			$this->_encodeGroup = 0x0E;
		}
		
		// Data Coding and Message Class
		if($this->_dataCodingAndMessageClass){
			// set bits to 1
			$this->_encodeGroup = 0x0F;
			
			// only class message
			$this->_dataEncoding = 0x03&$this->_classMessage;
			
			// check encoding
			switch($this->_alphabet){
				case self::ALPHABET_8BIT:
					$this->_dataEncoding |= (1<<2);
					break;
				case self::ALPHABET_DEFAULT:
					// bit is set to 0
					break;
				default:
					
					break;
					
			}
		}
		
		// return byte value
		return ((0x0F&$this->_encodeGroup)<<4) | (0x0F&$this->_dataEncoding);
	}
	
	/**
	 * method for cast to string
	 * @return string
	 */
	public function __toString()
	{
		return sprintf("%02X", $this->getValue());
	}
	
	/**
	 * Set store message
	 * @return \self
	 */
	public function setStoreMessage()
	{
		$this->_storeMessage = TRUE;
		return $this;
	}
	
	/**
	 * Set store message UCS2
	 * @return \self
	 */
	public function setStoreMessageUCS2()
	{
		$this->_storeMessageUCS2 = TRUE;
		return $this;
	}
	
	/**
	 * set message indication
	 * @param integer $indication
	 * @return \self
	 */
	public function setMessageIndication($indication)
	{
		$this->_messageIndication = (1 & $indication);
		return $this;
	}
	
	/**
	 * set message indication type
	 * @param integer $type
	 * @return \self
	 * @throws Exception
	 */
	public function setMessageIndicationType($type)
	{
		$this->_messageIndicationType = 0x03&$type;
		
		switch($this->_messageIndicationType){
			case self::INDICATION_TYPE_VOICEMAIL: 
				
				break;
			
			case self::INDICATION_TYPE_FAX:
				
				break;
			
			case self::INDICATION_TYPE_EMAIL:
				
				break;
			
			case self::INDICATION_TYPE_OTHER:
				
				break;
			
			default:
				throw new Exception("Wrong indication type");
		}
		
		return $this;
	}


	/**
	 * Set discard message
	 * @return \self
	 */
	public function setDiscardMessage()
	{
		$this->_discardMessage = TRUE;
		return $this;
	}


	/**
	 * set text is compressed
	 * @param boolean $compressed
	 * @return \self
	 */
	public function setTextCompressed($compressed = TRUE)
	{
		$this->_compressedText = $compressed;
		return $this;
	}
	
	/**
	 * get text is compressed
	 * @return boolean
	 */
	public function getTextCompressed()
	{
		return !!$this->_compressedText;
	}


	/**
	 * set text alphabet
	 * @param integer $alphabet
	 * @return \self
	 * @throws Exception
	 */
	public function setTextAlphabet($alphabet)
	{
		$this->_alphabet = (0x03&$alphabet);
		
		switch($this->_alphabet){
			case self::ALPHABET_DEFAULT:
				$this->setTextCompressed();
				break;
			
			case self::ALPHABET_8BIT:
				
				break;
			
			case self::ALPHABET_UCS2:
				
				break;
			
			case self::ALPHABET_RESERVED:
				
				break;
			
			default:
				throw new Exception("Wrong alphabet");
		}
		
		return $this;
	}
	
	/**
	 * getter text alphabet
	 * @return integer
	 */
	public function getTextAlphabet()
	{
		return $this->_alphabet;
	}
	
	/**
	 * change message class
	 * @param integer $class
	 * @return \self
	 * @throws Exception
	 */
	public function setClass($class)
	{
		$this->setUseMessageClass();
		$this->_classMessage = (0x03&$class);
		
		switch($this->_classMessage){
			case self::CLASS_NONE: 
				$this->setUseMessageClass(FALSE); 
				break;
			
			case self::CLASS_MOBILE_EQUIPMENT: 
				
				break;
			
			case self::CLASS_SIM_SPECIFIC_MESSAGE: 
				
				break;
			
			case self::CLASS_TERMINAL_EQUIPMENT: 
				
				break;
			
			default: 
				throw new Exception("Wrong class type");
		}
		
		return $this;
	}
	
	/**
	 * set use message class
	 * @return \self
	 * @param boolean $use
	 */
	public function setUseMessageClass($use = TRUE)
	{
		$this->_useMessageClass = $use;
		return $this;
	}
	
}