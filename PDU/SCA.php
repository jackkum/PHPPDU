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

class SCA {
	
	/**
	 * Type of number
	 * @var SCA\Type
	 */
	protected $_type   = NULL;
	
	/**
	 * Phone size
	 * @var integer
	 */
	protected $_size   = 0x00;
	
	
	/**
	 * phone number
	 * @var string
	 */
	protected $_phone  = NULL;
	
	/**
	 * recipient encoded
	 * @var type 
	 */
	protected $_encoded = NULL;


	/**
	 * how claclulate size (octets or digits)
	 * OA and DA size is on digits
	 * @var boolean
	 */
	protected $_isAddress = FALSE;
	
	/**
	 * create object
	 * @param boolean $isAddress 
	 */
	public function __construct($isAddress = TRUE)
	{
		// create sca type
		$this->setType(new SCA\Type());
		
		$this->_isAddress = !!$isAddress;
	}
	
	public static function parse($isAddress = TRUE)
	{
		$SCA     = new self($isAddress);
		$size    = hexdec(PDU::getPduSubstr(2));
		
		if($size){
			
			// if is OA or DA size in digits
			if($isAddress){
				if(($size % 2) != 0){
					$size++;
				}
			// else size in octets
			} else {
				$size--;
				$size *= 2;
			}
			
			$SCA->setType(
				new SCA\Type(
					hexdec(PDU::getPduSubstr(2))
				)
			);
			
			$hex = PDU::getPduSubstr($size);
			
			switch($SCA->getType()->getType()){
				case SCA\Type::TYPE_UNKNOWN: 
				case SCA\Type::TYPE_INTERNATIONAL:
				case SCA\Type::TYPE_ACCEPTER_INTO_NET:
				case SCA\Type::TYPE_SUBSCRIBER_NET:
				case SCA\Type::TYPE_TRIMMED:
					
					$SCA->setPhone(
						rtrim(
							implode(
								"",
								array_map(
									'strrev', 
									array_map(
										array('self', '_map_filter_decode'),
										str_split($hex, 2)
									)
								)
							),
							'F'
						)
					);
					
					break;
				
				case SCA\Type::TYPE_ALPHANUMERICAL:
					
					$SCA->setPhone(
						PDU\Helper::decode7bit($hex)
					);
					
					break;
				
			}
			
		}
		
		return $SCA;
	}

	/**
	 * getter for phone
	 * @return string|null
	 */
	public function getPhone()
	{
		return $this->_phone;
	}
	
	/**
	 * set phone number
	 * @param string $phone
	 * @param boolean $SC 
	 */
	public function setPhone($phone, $SC = FALSE)
	{
		$this->_phone     = $phone;
		$clear            = preg_replace('/([^a-c0-9\*\#]*)/', NULL, $phone);
		$this->_isAddress = !$SC;
		
		if($this->getType()->getType() == SCA\Type::TYPE_ALPHANUMERICAL){
			list($this->_size, $this->_encoded) = PDU\Helper::encode7bit($clear);
		} else {
			
			// get size
			// service center addres counting by octets OA or DA as length numbers
			$this->_size = $SC ? 1 + ((strlen($clear) + 1)/2) : strlen($clear);
			
			$this->_encoded = implode(
				"",
				array_map(                               // join filtered phone letters
					array('self', '_map_filter_encode'), // encode filter
					str_split($clear)
				)
			);
			
		}
		
	}
	
	/**
	 * getter for phone size
	 * @return integer
	 */
	public function getSize()
	{
		return $this->_size;
	}
	
	/**
	 * getter for phone type
	 * @return SCA\Type
	 */
	public function getType()
	{
		return $this->_type;
	}
	
	/**
	 * setter type
	 * @param SCA\Type $type
	 */
	public function setType(SCA\Type $type)
	{
		$this->_type = $type;
	}
	
	/**
	 * check is address
	 * @return boolean
	 */
	public function isAddress()
	{
		return !!$this->_isAddress;
	}
	
	/**
	 * magic method for cast to string
	 * @return srting|null
	 */
	public function __toString()
	{
		$PDU = sprintf("%02X", $this->getSize());
		
		if($this->getSize()){
			
			$PDU .= $this->getType();

			if($this->getType()->getType() != SCA\Type::TYPE_ALPHANUMERICAL){
				// reverse octets
				$l = strlen($this->_encoded);
				for($i = 0; $i < $l; $i += 2){
					$b1 = substr($this->_encoded, $i, 1);
					$b2 = substr($this->_encoded, $i+1, 1);
					
					if(!$b2){
						$b2 = 'F';
					}
					// add to pdu
					$PDU .= $b2 . $b1;
				}
			}
		}
		
		PDU::debug("SCA::__toString() " . $PDU);
		
		return $PDU;
	}
	
	/**
	 * get offset
	 * @return integer
	 */
	public function getOffset()
	{
		return ( ! $this->_size ? 2 : $this->_size + 4);
	}
	
	/**
	 * decode phone number
	 * @param string $letter
	 * @return string
	 */
	private static function _map_filter_decode($letter)
	{
		switch(hexdec($letter)){
			case 0x0A: return "*";
			case 0x0B: return "#";
			case 0x0C: return "a";
			case 0x0D: return "b";
			case 0x0E: return "c";
			default: return $letter;
		}
	}
	
	
	/**
	 * encode phone number
	 * @param string $letter
	 * @return string
	 */
	private static function _map_filter_encode($letter)
	{
		switch($letter){
			case "*": return 'A';
			case "#": return 'B';
			case "a": return 'C';
			case "b": return 'D';
			case "c": return 'E';
			default: return $letter;
		}
	}
	
}