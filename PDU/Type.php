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

require_once 'PDU/Type/Deliver.php';
require_once 'PDU/Type/Submit.php';

abstract class PDU_Type {
	
	const SMS_SUBMIT   = 0x01;
	const SMS_DELIVER  = 0x00;
	const SMS_REPORT   = 0x03;
	
	const VPF_NONE     = 0x00;
	const VPF_SIEMENS  = 0x01;
	const VPF_RELATIVE = 0x02;
	const VPF_ABSOLUTE = 0x03;
	
	/**
	 * Reply Path
	 * @var integer
	 */
	protected $_rp;
	
	
	/**
	 * User Data Header
	 * @var integer
	 */
	protected $_udhi;
	
	/**
	 * Status Report Request
	 * @var integer
	 */
	protected $_srr;
	
	/**
	 * Validity Period Format
	 * @var integer
	 */
	protected $_vpf;
	
	/**
	 * Reject Duplicates
	 * @var integer
	 */
	protected $_rd;
	
	/**
	 * Message Type Indicator
	 * @var integer
	 */
	protected $_mti;
	
	/**
	 * parse sms type
	 * @return \PDU_Type
	 * @throws Exception
	 */
	public static function parse()
	{
		$byte = hexdec(PDU::getPduSubstr(2));
		$type = NULL;
		
		switch((3&$byte)){
			case self::SMS_DELIVER:
				$type = new PDU_Type_Deliver();
				break;
			case self::SMS_SUBMIT:
				$type = new PDU_Type_Submit();
				break;
			case self::SMS_REPORT:
				throw new Exception("Not ready :(");
				//break;
			default:
				throw new Exception("Unknown type sms");
		}
		
		$type->_rp   = (1&$byte>>7);
		$type->_udhi = (1&$byte>>6);
		$type->_srr  = (1&$byte>>5);
		$type->_vpf  = (3&$byte>>3);
		$type->_rd   = (1&$byte>>2);
		$type->_mti  = (3&$byte);
		
		return $type;
		
	}
	
	/**
	 * Calculate byte value
	 * @return integer
	 */
	public function getValue()
	{
		return ((1 & $this->_rp)   << 7) | 
			   ((1 & $this->_udhi) << 6) | 
			   ((1 & $this->_srr)  << 5) | 
			   ((3 & $this->_vpf)  << 3) | 
			   ((1 & $this->_rd)   << 2) | 
			   ((3 & $this->_mti));
	}
	
	/**
	 * set validity period format
	 * @param integer $vpf
	 * @throws Exception
	 */
	public function setVpf($vpf)
	{
		$this->_vpf = (0x03&$vpf);
		
		switch($this->_vpf){
			case self::VPF_NONE: break;
			case self::VPF_SIEMENS: break;
			case self::VPF_RELATIVE: break;
			case self::VPF_ABSOLUTE: break;
			default: 
				throw new Exception("Wrong validity period format");
		}
	}
	
	/**
	 * getter for vpf
	 * @return integer
	 */
	public function getVpf()
	{
		return $this->_vpf;
	}
	
	/**
	 * set user data header
	 * @param type $udhi
	 */
	public function setUdhi($udhi)
	{
		$this->_udhi = (0x01&$udhi);
	}
	
	/**
	 * getter for udhi
	 * @return integer
	 */
	public function getUdhi()
	{
		return $this->_udhi;
	}
	
	/**
	 * getter for mti
	 * @return integer
	 */
	public function getMti()
	{
		return $this->_mti;
	}
	
	/**
	 * Magic method for cast to string
	 * @return string
	 */
	public function __toString()
	{
		return sprintf("%02X", $this->getValue());
	}
}