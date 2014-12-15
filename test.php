#!/usr/bin/php
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

/**
 * SMS-Submit
 * Description		SCA		|	PDU type	|	MR	|	DA	 |	PID		|	DCS		|	VP		|	UDL	| UD
 * size,bytes		1-12	|	1			|	1	|	2-12 |	1		|	1		|	0,1,7	|	1	| 0-140
 * 
 * SMS-Deliver
 * Description		SCA		|	PDU type	|	OA	|	PID	 |	DCS		|	SCTS	|	UDL
 * size,bytes		1-12	|	1			|	2-12|	1	 |	1		|	7		|	1		|	0-140
 * 
 * for check http://www.diafaan.com/sms-tutorials/gsm-modem-tutorial/online-sms-pdu-decoder/
 */

define('PDU_DEBUG', TRUE);
error_reporting(E_ALL);

set_exception_handler('exceptionHandler');

require_once 'Submit.php';

$pdu = new Submit();

$pdu->setAddress("79025449307");
$pdu->setData("long long long long long long long long long long long "
		. "long long long long long long long long long long long "
		. "long long long long long long long long long long long long long "
		. "long long long long long long long long long long long long "
		. "long long long long long long long long long long "
		. "long long long long long long long long long long "
		. "long long message...");

foreach($pdu->getParts() as $part){
	echo get_class($part), PHP_EOL;
	echo (string) $part, PHP_EOL;
}

function exceptionHandler($ex)
{
	exit( (string) $ex);
}