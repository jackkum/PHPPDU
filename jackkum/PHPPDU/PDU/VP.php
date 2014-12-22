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

class VP {
	
	/**
	 * date time validity period
	 * @var string|null
	 */
	protected $_datetime;
	
	/**
	 * inteval validity period
	 * @var integer|null
	 */
	protected $_interval;
	
	/**
	 * pdu message
	 * @var PDU
	 */
	protected $_pdu;
	
	/**
	 * create object
	 * @param PDU $pdu
	 */
	public function __construct(\jackkum\PHPPDU\Submit $pdu)
	{
		$this->_pdu = $pdu;
	}
	
	/**
	 * parse pdu string
	 * @param PDU $pdu
	 * @return \self
	 * @throws Exception
	 */
	public static function parse(PDU $pdu)
	{
		$vp = new self($pdu);
		
		switch($pdu->getType()->getVpf()){
			case PDU\Type::VPF_NONE:     return $vp;
			case PDU\Type::VPF_ABSOLUTE: return PDU\SCTS::parse();
			
			case PDU\Type::VPF_RELATIVE:
				
				$byte = hexdec(PDU::getPduSubstr(2));
				
				if($byte <= 143){
					$vp->_interval = ($byte+1) * (5*60);
				} else if($byte <= 167){
					$vp->_interval = (3600*24*12) + ($byte-143) * (30*60);
				} else if($byte <= 196) {
					$vp->_interval = ($byte-166) * (3600*24);
				} else {
					$vp->_interval = ($byte-192) * (3600*24*7);
				}
				
				return $vp;
			
			default:
				throw new Exception("Unknown VPF");
		}
	}


	/**
	 * getter for pdu message
	 * @return PDU
	 */
	public function getPdu()
	{
		return $this->_pdu;
	}
	
	/**
	 * set date time
	 * @param string $datetime
	 */
	public function setDateTime($datetime)
	{
		$this->_datetime = $datetime;
	}
	
	/**
	 * set interval
	 * @param type $interval
	 */
	public function setInterval($interval)
	{
		$this->_interval = $interval;
	}
	
	/**
	 * cast to string
	 * @return string
	 */
	public function __toString()
	{
		// get pdu type
		$type = $this->getPdu()->getType();
		
		// absolute value
		if($this->_datetime){
			$type->setVpf(PDU\Type::VPF_ABSOLUTE);
			return (string) (new SCTS($this->_datetime));
		}
		
		// relative value in seconds
		if($this->_interval){
			$type->setVpf(PDU\Type::VPF_RELATIVE);
			
			$minutes = ceil($this->_interval / 60);
			$hours   = ceil($this->_interval / 60 / 60);
			$days    = ceil($this->_interval / 60 / 60 / 24);
			$weeks   = ceil($this->_interval / 60 / 60 / 24 / 7);
			
			if($hours <= 12){
				return sprintf("%02X", ceil($minutes/5)-1);
			} else if($hours <= 24){
				return sprintf("%02X", ceil(($minutes-720)/30)+143);
			} else if($hours <= (30*24*3600)) {
				return sprintf("%02X", $days+166);
			} else {
				return sprintf("%02X", ($weeks > 63 ? 63 : $weeks)+192);
			}
		}
		
		// vpf not used
		$type->setVpf(PDU\Type::VPF_NONE);
		
		return "";
	}
}