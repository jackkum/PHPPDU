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
require_once 'PDU/Type/Report.php';

class Report extends PDU {
	
	/**
	 * referenced bytes
	 * @var integer
	 */
	protected $_reference;
	
	/**
	 * datetime
	 * @var PDU_SCTS
	 */
	protected $_timestamp;
	
	/**
	 * datetime
	 * @var PDU_SCTS
	 */
	protected $_discharge;
	
	/**
	 * report status
	 * 0x00 Short message received succesfully
	 * 0x01 Short message forwarded to the mobile phone, but unable to confirm delivery
	 * 0x02 Short message replaced by the service center
	 * 0x20 Congestion
	 * 0x21 SME busy
	 * 0x22 No response from SME
	 * 0x23 Service rejected
	 * 0x24 Quality of service not available
	 * 0x25 Error in SME
	 * 0x40 Remote procedure error
	 * 0x41 Incompatible destination
	 * 0x42 Connection rejected by SME
	 * 0x43 Not obtainable
	 * 0x44 Quality of service not available
	 * 0x45 No interworking available
	 * 0x46 SM validity period expired
	 * 0x47 SM deleted by originating SME
	 * 0x48 SM deleted by service center administration
	 * 0x49 SM does not exist
	 * 0x60 Congestion
	 * 0x61 SME busy
	 * 0x62 No response from SME
	 * 0x63 Service rejected
	 * 0x64 Quality of service not available
	 * 0x65 Error in SME
	 * 
	 * @var integer
	 */
	protected $_status;
	
	/**
	 * set pdu type
	 * @param array $params
	 */
	public function setType(array $params = array())
	{
		$this->_type = new PDU_Type_Report($params);
	}
	
	/**
	 * get a referenced bytes
	 * @return integer
	 */
	public function getReference()
	{
		return $this->_reference;
	}
	
	/**
	 * 
	 * @return PDU_SCTS
	 */
	public function getDateTime()
	{
		return $this->_timestamp;
	}
	
	/**
	 * 
	 * @return PDU_SCTS
	 */
	public function getDischarge()
	{
		return $this->_discharge;
	}
	
	/**
	 * status report
	 * @return integer
	 */
	public function getStatus()
	{
		return $this->_status;
	}
	
}