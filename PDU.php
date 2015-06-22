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

abstract class PDU {
	
	/**
	 * Service Centre Address
	 * @var PDU\SCA
	 */
	protected $_sca;
	
	/**
	 * Transport Protocol Data Unit
	 * @var PDU\Type
	 */
	protected $_type;
	
	/**
	 * Originator or Destination Address
	 * @var PDU\SCA
	 */
	protected $_address;
	
	/**
	 * Protoсol Identifier
	 * @var PDU\PID
	 */
	protected $_pid;
	
	/**
	 * Data Coding Scheme
	 * @var PDU\DCS
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
	 * parsed string
	 * @var string
	 */
	protected static $_pduParse;


	/**
	 * create pdu
	 */
	public function __construct()
	{
		$this->setSca();
		$this->initType();
		$this->setPid();
		$this->setDcs();
	}
	
	/**
	 * get a part pdu string and cut them from pdu
	 * @param integer $length
	 * @return string|null
	 */
	public static function getPduSubstr($length)
	{
		$str = mb_substr(self::$_pduParse, 0, $length);
		self::$_pduParse = mb_substr(self::$_pduParse, $length);
		return $str;
	}

	/**
	 * parse pdu string
	 * @param string $PDU
	 * @return PDU
	 * @throws Exception
	 */
	public static function parse($PDU)
	{
		// current pdu string
		self::$_pduParse = $PDU;
		
		// parse service center address
		$sca = PDU\SCA::parse(FALSE);
		
		// parse type of sms
		$self = PDU\Helper::getPduByType();
		
		// set sca
		$self->_sca = $sca;
		
		// parse sms address
		$self->_address = PDU\SCA::parse();
		
		return PDU\Helper::initVars($self);
	}

	/**
	 * getter for udl
	 * @return integer
	 */
	public function getUdl()
	{
		return $this->_udl;
	}
	
	/**
	 * setter for user data length
	 * @param type $udl
	 */
	public function setUdl($udl)
	{
		$this->_udl = $udl;
	}

	/**
	 * set sms center
	 * @param string|null|PDU\SCA $address
	 * @return PDU
	 */
	public function setSca($address = NULL)
	{
		
		if($address instanceof PDU\SCA){
			$this->_sca = $address;
			return;
		}
		
		if( ! $this->_sca){
			$this->_sca = new PDU\SCA(FALSE);
		}
		
		if($address){
			$this->_sca->setPhone($address, TRUE);
		}
		
		return $this;
	}
	
	/**
	 * getter for SCA
	 * @return PDU\SCA
	 */
	public function getSca()
	{
		return $this->_sca;
	}
	
	/**
	 * set pdu type
	 * @param array $params
	 * @return PDU
	 */
	abstract public function initType(array $params = array());
	
	/**
	 * abstract method for override on the child classes
	 * return start pdu string for split message on parts
	 * @return string
	 */
	abstract public function getStart();


	/**
	 * get pdu type
	 * @return PDU\Type
	 */
	public function getType()
	{
		return $this->_type;
	}
	
	/**
	 * setter for the type of pdu
	 * @param PDU\Type $type
	 */
	public function setType($type)
	{
		$this->_type = $type;
	}
	
	/**
	 * set address
	 * @param string|PDU\SCA $address
	 * @return PDU
	 */
	public function setAddress($address)
	{
		if($address instanceof PDU\SCA){
			$this->_address = $address;
			return $this;
		}
		
		$this->_address = new PDU\SCA();
		$this->_address->setPhone($address);
		return $this;
	}
	
	/**
	 * getter address
	 * @return PDU\SCA
	 */
	public function getAddress()
	{
		return $this->_address;
	}
	
	/**
	 * set Data Coding Scheme
	 * @param PDU\DCS $dcs
	 * @return PDU
	 */
	public function setDcs(PDU\DCS $dcs = NULL)
	{
		$this->_dcs = $dcs ? $dcs : new PDU\DCS();
		return $this;
	}
	
	/**
	 * getter for dcs
	 * @return PDU\DCS
	 */
	public function getDcs()
	{
		return $this->_dcs;
	}
	
	/**
	 * set data
	 * @param string|PDU\Data $data
	 * @return PDU
	 */
	public function setData($data)
	{
		if($data instanceof PDU\Data){
			$this->_ud = $data;
		} else {
			$this->_ud = new PDU\Data($this);
			$this->_ud->setData($data);
		}
		
		return $this;
	}
	
	/**
	 * getter user data
	 * @return PDU\Data
	 */
	public function getData()
	{
		return $this->_ud;
	}
	
	/**
	 * set pid
	 * @param integer $pid
	 * @return PDU
	 */
	public function setPid(PDU\PID $pid = NULL)
	{
		$this->_pid = $pid ? $pid : new PDU\PID();
		return $this;
	}
	
	/**
	 * get pid
	 * @return PDU/PID
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