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

require_once './jackkum/PHPPDU/Autoloader.php';

class PduTest extends PHPUnit_Framework_TestCase
{
    public function testSubmitCreate()
    {
		
		\jackkum\PHPPDU\Autoloader::register();

		$pdu = new \jackkum\PHPPDU\Submit();

		
		$pdu->setAddress("79025449307");
		
		$pdu->setData("long long long long long long long long long long long "
		. "long long long long long long long long long long long "
		. "long long long long long long long long long long long long long "
		. "long long long long long long long long long long long long "
		. "long long long long long long long long long long "
		. "long long long long long long long long long long "
		. "long long message...");

		$parts = $pdu->getParts();
		$this->assertNotNull($parts);
		$this->assertCount(3, $parts);
		
		foreach($parts as $part){
			$this->assertTrue($part instanceof jackkum\PHPPDU\PDU\Data\Part);
		}
		
		$this->assertTrue($pdu->getAddress()->getPhone() == '79025449307');
		
    }
	
	public function testSubmitParse()
	{
		$lines = array(
			'0061000B919720459403F700008C06080458F30301ECB7FB0C62BFDD6710FBED3E83D86FF719C47EBBCF20F6DB7D06B1DFEE3388FD769F41ECB7FB0C62BFDD6710FBED3E83D86FF719C47EBBCF20F6DB7D06B1DFEE3388FD769F41ECB7FB0C62BFDD6710FBED3E83D86FF719C47EBBCF20F6DB7D06B1DFEE3388FD769F41ECB7FB0C62BFDD6710FB0D',
			'0061000B919720459403F700008C06080458F30302EE3388FD769F41ECB7FB0C62BFDD6710FBED3E83D86FF719C47EBBCF20F6DB7D06B1DFEE3388FD769F41ECB7FB0C62BFDD6710FBED3E83D86FF719C47EBBCF20F6DB7D06B1DFEE3388FD769F41ECB7FB0C62BFDD6710FBED3E83D86FF719C47EBBCF20F6DB7D06B1DFEE3388FD769F41ECB7FB0C',
			'0061000B919720459403F700006306080458F3030320F6DB7D06B1DFEE3388FD769F41ECB7FB0C62BFDD6710FBED3E83D86FF719C47EBBCF20F6DB7D06B1DFEE3388FD769F41ECB7FB0C62BFDD6710FBED3E83D86FF719C47EBBCFA076793E0F9FCB2E970B'
		);
		
		foreach($lines as $i => $line){
			$pdu = \jackkum\PHPPDU\PDU::parse($line);
			
			// check instance
			$this->assertTrue($pdu instanceof jackkum\PHPPDU\Submit);
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
			// check number of part
			$this->assertTrue($part->getHeader()->getCurrent() == ($i+1));
		}
	}
	
	public function testReportParse()
	{
		$pdu = \jackkum\PHPPDU\PDU::parse('0006D60B911326880736F4111011719551401110117195714000');
		
		$this->assertTrue($pdu instanceof jackkum\PHPPDU\Report);
		$this->assertTrue('31628870634' == $pdu->getAddress()->getPhone());
		$this->assertTrue(1294739955    == $pdu->getDateTime()->getTime());
		$this->assertTrue(0             == $pdu->getStatus());
	}
	
	public function testDeliverParse()
	{
		$pdu = \jackkum\PHPPDU\PDU::parse('0791448720003023240DD0E474D81C0EBB010000111011315214000BE474D81C0EBB5DE3771B');
		
		$this->assertTrue($pdu instanceof jackkum\PHPPDU\Deliver);
		$this->assertTrue('diafaan'     == $pdu->getAddress()->getPhone());
		$this->assertTrue('diafaan.com' == $pdu->getData()->getData());
	}
	
}