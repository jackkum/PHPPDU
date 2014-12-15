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

class SCA_Type {
	
	const TYPE_UNKNOWN           = 0x00;
	const TYPE_INTERNATIONAL     = 0x01;
	const TYPE_NATIONAL          = 0x02;
	const TYPE_ACCEPTER_INTO_NET = 0x03;
	const TYPE_SUBSCRIBER_NET    = 0x04;
	const TYPE_ALPHANUMERICAL    = 0x05;
	const TYPE_TRIMMED           = 0x06;
	const TYPE_RESERVED          = 0x07;
	
	const PLAN_UNKNOWN           = 0x00;
	const PLAN_ISDN              = 0x01;
	const PLAN_X_121             = 0x02;
	const PLANL_TELEX            = 0x03;
	const PLAN_NATIONAL          = 0x08;
	const PLAN_INDIVIDUAL        = 0x09;
	const PLAN_ERMES             = 0x0A;
	const PLAN_RESERVED          = 0x0F;
	
	/**
	 * Type of number
	 * @var integer
	 */
	private $_type;
	
	/**
	 * Numbering plan identification
	 * @var integer
	 */
	private $_plan;
	
	/**
	 * Create type object
	 * @param integer $value
	 */
	public function __construct($value = 0x91)
	{
		$this->_type = 0x07 & ($value>>4);
		$this->_plan = 0x0F & $value;
	}
	
	/**
	 * setter for type of number
	 * @param type $type
	 */
	public function setType($type)
	{
		$this->_type = 0x07 & $type;
	}
	
	/**
	 * getter for type of number
	 * @return integer
	 */
	public function getType()
	{
		return $this->_type;
	}
	
	/**
	 * setter for numbering plan identification
	 * @param type $plan
	 */
	public function setPlan($plan)
	{
		$this->_plan = 0x0F & $plan;
	}
	
	/**
	 * getter for numbering plan identification
	 * @return integer
	 */
	public function getPlan()
	{
		return $this->_plan;
	}
	
	/**
	 * get current value
	 * @return integer
	 */
	public function getValue()
	{
		return (1 << 7) | ($this->getType() << 4) | $this->getPlan();
	}

	/**
	 * magic method cast to string
	 * @return string
	 */
	public function __toString()
	{
		return sprintf("%02X", $this->getValue());
	}
	
}