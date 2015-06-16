<?php

/* 
 * Copyright (C) 2015 jackkum
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

use jackkum\PHPPDU\PDU;

class PID {
	
	const PID_ASSIGNED   = 0x00; // Assigns bits 0..5 as defined below
	const PID_GSM_03_40  = 0x01; // See GSM 03.40 TP-PID complete definition
	const PID_RESERVED   = 0x02; // Reserved
	const PID_SPECIFIC   = 0x03; // Assigns bits 0-5 for SC specific use

	const TYPE_IMPLICIT  = 0x00; // Implicit
	const TYPE_TELEX     = 0x01; // telex (or teletex reduced to telex format)
	const TYPE_TELEFAX   = 0x02; // group 3 telefax
	const TYPE_VOICE     = 0x04; // voice telephone (i.e. conversion to speech)
	const TYPE_ERMES     = 0x05; // ERMES (European Radio Messaging System)
	const TYPE_NPS       = 0x06; // National Paging system (known to the SC
	const TYPE_X_400     = 0x11; // any public X.400-based message handling system
	const TYPE_IEM       = 0x12; // Internet Electronic Mail
	
	/**
	 * pid value
	 * @var integer
	 */
	protected $_pid = self::PID_ASSIGNED;
	
	/**
	 * value = 0 : no interworking, but SME-to-SME protocol
	 * value = 1 : telematic interworking
	 * @var integer
	 */
	protected $_indicates = 0x00;
	
	/**
	 * type value
	 * @var integer
	 */
	protected $_type = self::TYPE_IMPLICIT;
	
	public static function parse()
	{
		$byte = hexdec(PDU::getPduSubstr(2));
		$self = new self();
		$self->setPid($byte >> 6);
		$self->setIndicates($byte >> 5);
		$self->setType($byte);
		
		return $self;
	}
	
	/**
	 * getter for the pid
	 * @return integer
	 */
	public function getPid()
	{
		return $this->_pid;
	}
	
	/**
	 * setter for the pid
	 * @param integer $pid
	 */
	public function setPid($pid)
	{
		$this->_pid = 0x03 & $pid;
	}
	
	/**
	 * getter for the indicates
	 * @return integer
	 */
	public function getIndicates()
	{
		return $this->_indicates;
	}
	
	/**
	 * setter for the indicates
	 * @param integer $indicates
	 */
	public function setIndicates($indicates)
	{
		$this->_indicates = 0x01 & $indicates;
	}
	
	/**
	 * getter for the type
	 * @return integer
	 */
	public function getType()
	{
		return $this->_type;
	}
	
	/**
	 * setter for the type
	 * @param integer $type
	 */
	public function setType($type)
	{
		$this->_type = 0x1F & $type;
	}
	
	/**
	 * getter for ready value
	 * @return integer
	 */
	public function getValue()
	{
		return ($this->_pid << 6) | ($this->_indicates << 5) | $this->_type;
	}
	
	/**
	 * cast to string
	 * @return string
	 */
	public function __toString()
	{
		return (string) $this->getValue();
	}
}