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

class Deliver extends PDU {
	
	/**
	 * 
	 * @var PDU\SCTS
	 */
	protected $_scts;
	
	/**
	 * create deliver
	 */
	public function __construct() 
	{
		parent::__construct();
		
		$this->setScts();
	}
	
	/**
	 * set scts
	 * @param string|null|PDU\SCTS $time
	 * @return \Deliver
	 */
	public function setScts($time = NULL)
	{
		if($time instanceof PDU\SCTS){
			$this->_scts = $time;
		} else {
			$this->_scts = new PDU\SCTS(is_null($time) ? $this->_getDateTime() : $time);
		}
		
		return $this;
	}
	
	/**
	 * getter for scts
	 * @return PDU\SCTS
	 */
	public function getScts()
	{
		return $this->_scts;
	}
	
	/**
	 * get default datetime
	 * @return string
	 */
	protected function _getDateTime()
	{
		// 10 days
		return date('Y-m-d H:i:s', time() + (3600*24*10));
	}
	
	/**
	 * set pdu type
	 * @param array $params
	 * @return \Deliver
	 */
	public function initType(array $params = array())
	{
		$this->_type = new PDU\Type\Deliver($params);
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
		$PDU .= (string) $this->getAddress();
		$PDU .= sprintf("%02X", $this->getPid()->getValue());
		$PDU .= (string) $this->getDcs();
		$PDU .= (string) $this->getScts();
		
		return $PDU;
	}
}