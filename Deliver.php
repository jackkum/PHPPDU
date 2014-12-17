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

require_once 'PDU.php';
require_once 'PDU/Type/Deliver.php';

class Deliver extends PDU {
	
	/**
	 * 
	 * @var PDU_SCTS
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
	 * @param string|null $time
	 * @return \Deliver
	 */
	public function setScts($time = NULL)
	{
		$this->_scts = new PDU_SCTS(is_null($time) ? $this->_getDateTime() : $time);
		return $this;
	}
	
	/**
	 * getter for scts
	 * @return PDU_SCTS
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
	public function setType(array $params = array())
	{
		$this->_type = new PDU_Type_Deliver($params);
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
		$PDU .= sprintf("%02X", $this->getPid());
		$PDU .= (string) $this->getDcs();
		$PDU .= (string) $this->getScts();
		
		return $PDU;
	}
}