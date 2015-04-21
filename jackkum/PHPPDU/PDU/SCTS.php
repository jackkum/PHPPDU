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

use jackkum\PHPPDU\PDU;

class SCTS {
	
	/**
	 * unix time
	 * @var integer
	 */
	protected $_time;
	
	/**
	 * create datetime
	 * @param string $datetime
	 */
	public function __construct($datetime)
	{
		$this->_time = strtotime($datetime);
	}
	
	/**
	 * parse pdu string
	 * @return \self
	 */
	public static function parse()
	{
		$hex    = PDU::getPduSubstr(14);
		$params = array_merge(
			array("20%02d-%02d-%02d %02d:%02d:%02d"),
			array_map('intval', array_map('strrev', str_split($hex,2)))
		);
		
		$dtime = call_user_func_array('sprintf', $params);
		
		return new self($dtime);
	}
	
	/**
	 * getter time
	 * @return integer
	 */
	public function getTime()
	{
		return $this->_time;
	}
	
	/**
	 * format datatime for split
	 * @return string
	 */
	protected function _getDateTime()
	{
		return gmdate('ymdHis00', $this->getTime());
	}

	/**
	 * cast to string
	 * @return string
	 */
	public function __toString() 
	{
		return implode(
			"",	
			array_map(
				'strrev',
				str_split(
					$this->_getDateTime(),
					2
				)
			)
		);
	}
	
}