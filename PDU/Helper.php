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
use jackkum\PHPPDU\Submit;
use jackkum\PHPPDU\Report;
use jackkum\PHPPDU\Deliver;

class Helper {
	
	/**
	 * ord function
	 * @link http://ru.php.net/ord
	 * @param string $c
	 * @param integer $index
	 * @param integer $bytes
	 * @return integer|boolean
	 */
	public static function ordUTF8($c, $index = 0, &$bytes = null)
	{
		$len = strlen($c);
		$bytes = 0;
		
		if($index >= $len){
			return false;
		}
		
		$h = ord($c{$index});
		
		if($h <= 0x7F){
			$bytes = 1;
			return $h;
		} else if ($h < 0xC2){
			return false;
		} else if ($h <= 0xDF && $index < $len - 1) {
			$bytes = 2;
			return ($h & 0x1F) <<  6 | (ord($c{$index + 1}) & 0x3F);
		} else if ($h <= 0xEF && $index < $len - 2) {
			$bytes = 3;
			return ($h & 0x0F) << 12 | (ord($c{$index + 1}) & 0x3F) << 6 | (ord($c{$index + 2}) & 0x3F);
		} else if ($h <= 0xF4 && $index < $len - 3) {
			$bytes = 4;
			return ($h & 0x0F) << 18 | (ord($c{$index + 1}) & 0x3F) << 12
                             | (ord($c{$index + 2}) & 0x3F) << 6
                             | (ord($c{$index + 3}) & 0x3F);
		} else {
			return false;
		}
	}
	
	/**
	 * chr function 
	 * @link http://php.net/manual/ru/function.chr.php#88611
	 * @param integer $u
	 * @return string
	 */
	public static function chrUTF8($u)
	{
		return mb_convert_encoding('&#' . intval($u) . ';', 'UTF-8', 'HTML-ENTITIES');
	}
	
	/**
	 * decode message from unicode
	 * @param string $text
	 * @return srting
	 */
	public static function decode16Bit($text)
	{
		return implode(
			"",
			array_map(
				array('self', 'chrUTF8'),
				array_map(
					'hexdec',
					str_split(
						$text, 
						4
					)
				)
			)
		);
	}
	
	/**
	 * decode message
	 * @param string $text
	 * @return string
	 */
	public static function decode8Bit($text)
	{
		return implode(
			"",
			array_map(
				array('self', 'chrUTF8'),
				array_map(
					'hexdec',
					str_split(
						$text, 
						2
					)
				)
			)
		);
	}
	
	/**
	 * decode message from 7bit
	 * @param string $text
	 * @return string
	 */
	public static function decode7bit($text)
	{
		$ret = '';
		$data = str_split(pack('H*', $text));
	
		$mask = 0xFF;
		$shift = 0;
		$carry = 0;
		
		foreach($data as $char) {
			if($shift == 7){
				$ret .= chr($carry);
				$carry = 0;
				$shift = 0;
			}
			
			$a = ($mask >> ($shift+1)) & 0xFF;
			$b = $a ^ 0xFF;
	
			$digit = ($carry) | ((ord($char) & $a) << ($shift)) & 0xFF;
			$carry = (ord($char) & $b) >> (7-$shift);
			$ret .= chr($digit);
	
			$shift++;
		}
		
		if ($carry){
			$ret .= chr($carry);
		}
		
		return $ret;
	}
	
	/**
	 * encode message
	 * @param string $text
	 * @return array
	 */
	public static function encode8Bit($text)
	{
		$length = 0;
		$pdu    = NULL;
		for($i = 0; $i < strlen($text); $i++){
			$pdu .= sprintf("%02X", ord(substr($text, $i, 1)));
			$length++;
		}
		
		return array($length, $pdu);
	}
	
	/**
	 * encode message
	 * @param string $text
	 * @return array
	 */
	public static function encode7bit($text)
	{
		$ret   = '';
		$data  = str_split($text);
		$mask  = 0xFF;
		$shift = 0;
		$len   = count($data);
		
		for ($i = 0; $i < $len; $i++) {
			
			$char     = ord($data[$i]) & 0x7F;
			$nextChar = ($i+1 < $len) ? (ord($data[$i+1]) & 0x7F) : 0;
			
			if ($shift == 7) { $shift = 0; continue; }
			
			$carry  = ($nextChar & ((($mask << ($shift+1)) ^ 0xFF) & 0xFF));
			$digit  = (($carry << (7-$shift)) | ($char >> $shift) ) & 0xFF;
			$ret   .= chr($digit);
			
			$shift++;
		}
		
		$str = unpack('H*', $ret);
		
		return array($len, strtoupper($str[1]));
	}
	
	/**
	 * encode message
	 * @param string $text
	 * @return array
	 */
	public static function encode16Bit($text)
	{
		$length = 0;
		$pdu    = NULL;
		
		for($i = 0; $i < mb_strlen($text); $i++){
			$byte = ord(mb_substr($text, $i, 1));
			$pdu .= sprintf("%04X", $byte);
			$length++;
		}
		
		return array($length, $pdu);
	}
	
	/**
	 * get pdu object by type
	 * @return Deliver|Submit|Report
	 * @throws Exception
	 */
	public static function getPduByType()
	{
		// parse type of sms
		$type = Type::parse();
		$self = NULL;
		
		switch($type->getMti()){
			case Type::SMS_DELIVER:
				$self = new Deliver();
				break;
		
			case Type::SMS_SUBMIT:
				$self = new Submit();
				// get mr
				$self->setMr(hexdec(PDU::getPduSubstr(2)));
				break;
			
			case Type::SMS_REPORT:
				$self = new Report();
				// get reference
				$self->setReference(hexdec(PDU::getPduSubstr(2)));
				break;
			
			default:
				throw new Exception("Unknown sms type");
				
		}
		
		// set type
		$self->setType($type);
		
		return $self;
	}
	
	public static function initVars(PDU $pdu)
	{
		// if is the report status
		if($pdu->getType() instanceof Type\Report){
			// parse timestamp
			$pdu->setDateTime(SCTS::parse());
			
			// parse discharge
			$pdu->setDischarge(SCTS::parse());
			
			// get status
			$pdu->setStatus(hexdec(PDU::getPduSubstr(2)));
		} else {
			// get pid
			$pdu->setPid(hexdec(PDU::getPduSubstr(2)));

			// parse dcs
			$pdu->setDcs(DCS::parse());

			// if this submit sms
			if($pdu->getType() instanceof Type\Submit){
				// parse vp
				$pdu->setVp(VP::parse($pdu));
			} else {
				// parse scts
				$pdu->setScts(SCTS::parse());
			}

			// get data length
			$pdu->setUdl(hexdec(PDU::getPduSubstr(2)));

			// parse data
			$pdu->setData(Data::parse($pdu));
		}
		
		return $pdu;
	}
}