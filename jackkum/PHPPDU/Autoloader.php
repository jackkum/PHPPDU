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

class Autoloader {
	
	public static function register()
	{
		\spl_autoload_register('jackkum\PHPPDU\Autoloader::aoutload');
	}

	public static function aoutload($file)
	{
		$path = __DIR__ . 
				DIRECTORY_SEPARATOR . '..' . 
				DIRECTORY_SEPARATOR . ".." . 
				DIRECTORY_SEPARATOR;
		
		$filepath = $path . str_replace('\\', '/', $file) . '.php';
		echo "# " . $filepath . "\n";

		if( ! file_exists($filepath)){
			$filepath = $path . 'jackkum'.DIRECTORY_SEPARATOR.'PHPPDU'.DIRECTORY_SEPARATOR . str_replace('\\', '/', $file) . '.php';
			echo "# " . $filepath . "\n";
		}
		
		require_once($filepath);
	}
}