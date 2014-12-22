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

namespace jackkum\PHPPDU\PDU\Type;

use jackkum\PHPPDU\PDU;

class Report extends PDU\Type {
	
	public function __construct(array $params = array())
	{
		$this->_rp   = isset($params['rp'])   ? 1 & $params['rp']   : 0;
		$this->_udhi = isset($params['udhi']) ? 1 & $params['udhi'] : 0;
		$this->_srr  = isset($params['srr'])  ? 1 & $params['srr']  : 0;
		
		//More Message to Send
		$this->_rd   = isset($params['mms'])  ? 1 & $params['mms']   : 0;
		$this->_mti  = 0x02; // SMS-REPORT
		$this->_vpf  = 0x00; // not used
	}
}