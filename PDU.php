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

require_once 'SCA.php';
require_once 'PDU/DCS.php';
require_once 'PDU/Helper.php';
require_once 'PDU/Data.php';

abstract class PDU {
	
	/**
	 * Service Centre Address
	 * @var SCA
	 */
	protected $_sca;
	
	/**
	 * Transport Protocol Data Unit
	 * @var PDU_Type
	 */
	protected $_type;
	
	/**
	 * Originator or Destination Address
	 * @var SCA
	 */
	protected $_address;
	
	/**
	 * Protoсol Identifier
	 * @var integer
	 */
	protected $_pid = 0x00;
	
	/**
	 * Data Coding Scheme
	 * @var PDU_DCS
	 */
	protected $_dcs;
	
	/**
	 * User Data Length
	 * @var integer
	 */
	protected $_udl;
	
	/**
	 * User Data
	 * @var string
	 */
	protected $_ud;
	
	/**
	 * create pdu
	 */
	public function __construct()
	{
		$this->setSca();
		$this->setType();
		$this->setDcs();
	}
	
	/**
	 * set sms center
	 * @param string|null $number
	 * @return \PDU
	 */
	public function setSca($number = NULL)
	{
		if( ! $this->_sca){
			$this->_sca = new SCA();
		}
		
		if($number){
			$this->_sca->setPhone($number);
		}
		
		return $this;
	}
	
	/**
	 * getter for SCA
	 * @return SCA
	 */
	public function getSca()
	{
		return $this->_sca;
	}
	
	/**
	 * set pdu type
	 * @param array $params
	 * @return \PDU
	 */
	abstract public function setType(array $params = array());
	
	/**
	 * get pdu type
	 * @return PDU_Type
	 */
	public function getType()
	{
		return $this->_type;
	}
	
	/**
	 * set address
	 * @param string $number
	 * @return \PDU
	 */
	public function setAddress($number)
	{
		$this->_address = new SCA();
		$this->_address->setPhone($number);
		return $this;
	}
	
	/**
	 * getter address
	 * @return SCA
	 */
	public function getAddress()
	{
		return $this->_address;
	}
	
	/**
	 * set Data Coding Scheme
	 * @return \Submit
	 */
	public function setDcs()
	{
		$this->_dcs = new PDU_DCS();
		return $this;
	}
	
	/**
	 * getter for dcs
	 * @return PDU_DCS
	 */
	public function getDcs()
	{
		return $this->_dcs;
	}
	
	/**
	 * set data
	 * @param string $data
	 * @return \Submit
	 */
	public function setData($data)
	{
		$this->_ud = new PDU_Data($this);
		$this->_ud->setData($data);
		return $this;
	}
	
	/**
	 * getter user data
	 * @return PDU_Data
	 */
	public function getData()
	{
		return $this->_ud;
	}
	
	/**
	 * set pid
	 * @param integer $pid
	 * @return \PDU
	 */
	public function setPid($pid)
	{
		$this->_pid = 0xFF&$pid;
		return $this;
	}
	
	/**
	 * get pid
	 * @return integer
	 */
	public function getPid()
	{
		return $this->_pid;
	}
	
	/**
	 * get parts sms
	 * @return array
	 */
	public function getParts()
	{
		if( ! $this->getAddress()){
			throw new Exception("Address not set");
		}
		
		if( ! $this->getData()){
			throw new Exception("Data not set");
		}
		
		return $this->getData()->getParts();
	}
	
	public static function debug($message)
	{
		if( ! defined('PDU_DEBUG')){
			return;
		}
		
		echo "# " .date('H:i:s') . " - " . $message . "\n";
	}
}