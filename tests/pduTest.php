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

		foreach($pdu->getParts() as $part){
			$this->assertTrue($part instanceof jackkum\PHPPDU\PDU\Data\Part);
		}
		
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
			
			$part = array_shift($pdu->getParts());
			// check current part of pdu
			$this->assertNotNull($part);
			// check number of part
			$this->assertTrue($part->getHeader()->getCurrent() == ($i+1));
			
		}
	}
}