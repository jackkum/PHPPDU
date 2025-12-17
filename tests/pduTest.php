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

require_once dirname(__FILE__).'/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use jackkum\PHPPDU\PDU;
use jackkum\PHPPDU\Submit;
use jackkum\PHPPDU\Report;
use jackkum\PHPPDU\Deliver;

define('PDU_DEBUG', true);

class PduTest extends TestCase
{
	protected static $lines = array();

	protected static $message = "long \nlong long long long long long long long long long long long long long long long long long long long long long long long long long long long long long long long long long long long long long long long long long long long long long long long long long long long long long long long long long long long long long long long long long long long message...";

	public function testSubmitCreate()
	{
		$pdu = new Submit();

		$pdu->setAddress("79025449307");

		$pdu->setData(self::$message);

		$parts = $pdu->getParts();
		$this->assertNotNull($parts);
		$this->assertCount(3, $parts);

		foreach($parts as $part){
			$this->assertTrue($part instanceof PDU\Data\Part);
			self::$lines[] = (string) $part;
		}

		$this->assertTrue($pdu->getAddress()->getPhone() == '79025449307');

	}
	
	public function testSCA()
	{
		$pdu = new Submit();
		$pdu->setSca("+31653131313");
		$pdu->setAddress("+48660548430");
		$pdu->setData("łóśćąę");
		
		$parts = $pdu->getParts();
		$this->assertNotNull($parts);
		$this->assertCount(1, $parts);

		foreach($parts as $part){
			echo (string)$part . " == 07911356131313F301000B918466508434F000080C014200F3015B010701050119\n";
			$this->assertTrue(((string)$part) == '07911356131313F301000B918466508434F000080C014200F3015B010701050119');
		}
	}
	
	public function testPolishChar()
	{
		$pdu = new Submit();
		$pdu->setAddress("+48660548430");
		$pdu->setVp(3600 * 24 * 4);
		$pdu->setData("łóśćąę"); // łóśćąę 
		
		$parts = $pdu->getParts();
		$this->assertNotNull($parts);
		$this->assertCount(1, $parts);

		foreach($parts as $part){
			echo (string)$part . " == 07911356131313F301000B918466508434F000080C014200F3015B010701050119\n";
			$this->assertTrue(((string)$part) == '0011000B918466508434F00008AA0C014200F3015B010701050119');
		}
	}

	public function testSubmitParse()
	{
		$main = NULL;
		
		foreach(self::$lines as $i => $line){
			$pdu = PDU::parse($line);
			var_dump(get_class($pdu));
			// check instance
			$this->assertTrue($pdu instanceof Submit);
			// check phone mumber
			$this->assertTrue($pdu->getAddress()->getPhone() == '79025449307');
			// get parts
			$parts = $pdu->getParts();
			// check parts
			$this->assertNotNull($parts);
			// first part
			$part  = array_shift($parts);
			// check current part of pdu
			$this->assertNotNull($part);
			
			if(is_null($main)){
				// get current pdu
				$main = $pdu;
			} else {
				// append part
				$main->getData()->append($pdu);
			}
		}
		
		// check text message
		$this->assertTrue($main->getData()->getData() == self::$message);
		
	}
	
	public function testReportParse()
	{
		$pdu = PDU::parse('0006D60B911326880736F4111011719551401110117195714000');
		
		$this->assertTrue($pdu instanceof Report);
		$this->assertTrue('31628870634' == $pdu->getAddress()->getPhone());
		$this->assertTrue(0             == $pdu->getStatus());
	}
	
	public function testDeliverParse()
	{
		$pdu = PDU::parse('0791448720003023240DD0E474D81C0EBB010000111011315214000BE474D81C0EBB5DE3771B');
		
		$this->assertTrue($pdu instanceof Deliver);
		$this->assertTrue('diafaan'     == $pdu->getAddress()->getPhone());
		$this->assertTrue('diafaan.com' == $pdu->getData()->getData());
	}
	
}