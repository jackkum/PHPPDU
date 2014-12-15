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

require_once 'PDU/Data/Header.php';

class PDU_Data_Part {
	
	/**
	 * header message
	 * @var PDU_Data_Header
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
	 * @var PDU_Data
	 */
	protected $_parent;
	
	/**
	 * create data part
	 * @param string $data
	 * @param array|NULL $header
	 */
	public function __construct(PDU_Data $parent, $data, $size, $header = NULL)
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
			$this->_header = new PDU_Data_Header($header);
		}
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
	 * @return string
	 */
	public function getHeader()
	{
		return (string) $this->_header;
	}
	
	/**
	 * getter parent of part
	 * @return PDU_Data
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
		
		PDU::debug("_getPduString() " . $this->_getPduString());
		PDU::debug("_getPartSize() " . $this->_getPartSize());
		PDU::debug("getHeader() " . $this->getHeader());
		PDU::debug("getData() " . $this->getData());
		
		// concate pdu, size of part, headers, data
		return $this->_getPduString() . 
			   $this->_getPartSize()  . 
			   $this->getHeader()     . 
			   $this->getData();
	}
	
}