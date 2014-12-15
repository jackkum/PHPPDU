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

require_once 'SCA/Type.php';

class SCA {
	
	/**
	 * Type of number
	 * @var SCA_Type
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
	 * create object
	 */
	public function __construct()
	{
		// create sca type
		$this->setType(new SCA_Type());
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
	 */
	public function setPhone($phone)
	{
		$this->_phone = implode(
			"",
			array_map(                              // join filtered phone letters
				array($this, '_map_filter_encode'), // encode filter
				str_split(                          // split string
					preg_replace(                   // replace wrong letters
						'/([^a-c0-9\*\#]*)/', 
						NULL, 
						$phone
					)
				)
			)
		);
		
		// get size
		$this->_size = strlen($this->_phone);
		
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
	 * @return SCA_Type
	 */
	public function getType()
	{
		return $this->_type;
	}
	
	/**
	 * setter type
	 * @param SCA_Type $type
	 */
	public function setType(SCA_Type $type)
	{
		$this->_type = $type;
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

			// reverse octets
			for($i = 0; $i < $this->getSize(); $i += 2){
				$b1 = substr($this->getPhone(), $i, 1);
				$b2 = substr($this->getPhone(), $i+1, 1);

				if($b2 === FALSE){
					$b2 = 'F';
				}
				// add to pdu
				$PDU .= $b2 . $b1;
			}
		}
		
		PDU::debug("SCA::__toString() " . $PDU);
		
		return $PDU;
	}
	
	/**
	 * decode phone number
	 * @param string $letter
	 * @return string
	 */
	private function _map_filter_decode($letter)
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
	private function _map_filter_encode($letter)
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