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

namespace jackkum\PHPPDU;

define('PDU_BASE_PATH', realpath(dirname(__FILE__)));

class Autoloader {
	
	const VENDOR  = 'jackkum';
	const PROJECT = 'PHPPDU';
	
	public static function register()
	{
		\spl_autoload_register('jackkum\PHPPDU\Autoloader::aoutload');
	}

	public static function aoutload($file)
	{
		$parts = explode("\\", $file);
		
		if($parts[0] != self::VENDOR){
			return;
		}
		
		array_shift($parts);
		
		if($parts[0] != self::PROJECT){
			return;
		}
		
		array_shift($parts);
		
		$path = PDU_BASE_PATH . DIRECTORY_SEPARATOR;
		
		$filepath = $path . implode(DIRECTORY_SEPARATOR, $parts) . '.php';
		
		if(file_exists($filepath)){
			require_once($filepath);
		}
		
	}
}