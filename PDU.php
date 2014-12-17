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
require_once 'Submit.php';
require_once 'Deliver.php';
require_once 'PDU/DCS.php';
require_once 'PDU/SCTS.php';
require_once 'PDU/Helper.php';
require_once 'PDU/Data.php';
require_once 'PDU/VP.php';

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
		$this->setType();
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
	 * @return \PDU
	 * @throws Exception
	 */
	public static function parse($PDU)
	{
		// current pdu string
		self::$_pduParse = $PDU;
		
		// parse service center address
		$sca = SCA::parse();
		
		// parse type of sms
		$type = PDU_Type::parse();
		
		switch($type->getMti()){
			case PDU_Type::SMS_DELIVER:
				$self = new Deliver();
				break;
		
			case PDU_Type::SMS_SUBMIT:
				$self = new Submit();
				break;
			
			case PDU_Type::SMS_REPORT:
				
				break;
			
			default:
				throw new Exception("Unknown sms type");
		}
		
		// set sca
		$self->_sca = $sca;
		// set type
		$self->_type = $type;
		
		// if this is submit type
		if($self->_type instanceof PDU_Type_Submit){
			// get mr
			$self->_mr = hexdec(PDU::getPduSubstr(2));
		}
		
		// parse sms address
		$self->_address = SCA::parse();
		
		// get pid
		$self->_pid = hexdec(PDU::getPduSubstr(2));
		
		// parse dcs
		$self->_dcs = PDU_DCS::parse();
		
		// if this submit sms
		if($self->_type instanceof PDU_Type_Submit){
			// parse vp
			$self->_vp = PDU_VP::parse($self);
		} else {
			// parse scts
			$self->_scts = PDU_SCTS::parse();
		}
		
		// get data length
		$self->_udl = hexdec(PDU::getPduSubstr(2));
		
		// parse data
		$self->_ud = PDU_Data::parse($self);
		
		return $self;
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
	 * @return \PDU
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
	 * @return \PDU
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