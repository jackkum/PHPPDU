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

namespace jackkum\PHPPDU;

class Submit extends PDU {
	
	/**
	 * Message Reference
	 * not changed for submit message
	 * @var integer
	 */
	protected $_mr = 0x00;
	
	/**
	 * Validity Period
	 * @var PDU\VP
	 */
	protected $_vp;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->setVp();
	}
	
	/**
	 * set validity period
	 * @param string|int $value
	 * @return \Submit
	 */
	public function setVp($value = NULL)
	{
		$this->_vp = new PDU\VP($this);
		
		if(is_string($value)){
			$this->_vp->setDateTime($value);
		} else {
			$this->_vp->setInterval($value);
		}
		
		return $this;
	}
	
	/**
	 * getter validity period
	 * @return PDU\VP
	 */
	public function getVp()
	{
		return $this->_vp;
	}
	
	/**
	 * getter message reference
	 * @return integer
	 */
	public function getMr()
	{
		return $this->_mr;
	}
	
	/**
	 * set pdu type
	 * @param array $params
	 * @return \Submit
	 */
	public function setType(array $params = array())
	{
		$this->_type = new PDU\Type\Submit($params);
		return $this;
	}
	
	/**
	 * Magic method for cast to string
	 * @return string
	 */
	public function __toString()
	{
		$PDU  = (string) $this->getSca();
		$PDU .= (string) $this->getType();
		$PDU .= sprintf("%02X", $this->getMr());
		$PDU .= (string) $this->getAddress();
		$PDU .= sprintf("%02X", $this->getPid());
		$PDU .= (string) $this->getDcs();
		$PDU .= (string) $this->getVp();
		
		return $PDU;
	}
}