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

class PDU_Data_Header {
	
	/**
	 * 
	 * @var integer
	 */
	protected $_UDHL     = 6;
	
	/**
	 *
	 * @var integer
	 */
	protected $_TYPE     = 0x08; // 16bit
	
	/**
	 *
	 * @var integer
	 */
	protected $_PSIZE    = 4;
	
	/**
	 *
	 * @var integer
	 */
	protected $_POINTER  = 0;
	
	/**
	 *
	 * @var integer
	 */
	protected $_SEGMENTS = 1;
	
	/**
	 *
	 * @var integer
	 */
	protected $_CURRENT  = 1;
	
	/**
	 * create header
	 * @param array $params
	 */
	public function __construct(array $params)
	{
		$this->_SEGMENTS = isset($params['SEGMENTS']) ? $params['SEGMENTS'] : 1;
		$this->_CURRENT  = isset($params['CURRENT'])  ? $params['CURRENT']  : 1;
		$this->_POINTER  = isset($params['POINTER'])  ? $params['POINTER']  : rand(0, 65535);
	}
	
	/**
	 * parse header
	 * @return \self
	 */
	public static function parse()
	{
		$self = new self(
			array(
				'UDHL'     => hexdec(PDU::getPduSubstr(2)),
				'TYPE'     => hexdec(PDU::getPduSubstr(2)),
				'PSIZE'    => hexdec(PDU::getPduSubstr(2)),
				'POINTER'  => hexdec(PDU::getPduSubstr(4)),
				'SEGMENTS' => hexdec(PDU::getPduSubstr(2)),
				'CURRENT'  => hexdec(PDU::getPduSubstr(2))
			)
		);
		
		return $self;
	}
	
	/**
	 * cast object to array
	 * @return array
	 */
	public function toArray()
	{
		return array(
			'UDHL'     => $this->_UDHL,
			'TYPE'     => $this->_TYPE,
			'PSIZE'    => $this->_PSIZE,
			'POINTER'  => $this->_POINTER,
			'SEGMENTS' => $this->_SEGMENTS,
			'CURRENT'  => $this->_CURRENT
		);
	}
	
	/**
	 * get header size
	 * @return integer
	 */
	public function getSize()
	{
		return $this->_UDHL;
	}
	
	/**
	 * get header type
	 * @return integer
	 */
	public function getType()
	{
		return $this->_TYPE;
	}
	
	/**
	 * get a pointer size
	 * @return integer
	 */
	public function getPointerSize()
	{
		return $this->_PSIZE;
	}
	
	/**
	 * get a pointer
	 * @return integer
	 */
	public function getPointer()
	{
		return $this->_POINTER;
	}
	
	/**
	 * get a segments
	 * @return integer
	 */
	public function getSegments()
	{
		return $this->_SEGMENTS;
	}
	
	/**
	 * get current segment
	 * @return integer
	 */
	public function getCurrent()
	{
		return $this->_CURRENT;
	}
	
	/**
	 * method for cast to string
	 * @return string
	 */
	public function __toString()
	{
		$HEAD  = sprintf("%02X", $this->_UDHL);
		$HEAD .= sprintf("%02X", $this->_TYPE);
		$HEAD .= sprintf("%02X", $this->_PSIZE);
		$HEAD .= sprintf("%04X", $this->_POINTER);
		$HEAD .= sprintf("%02X", $this->_SEGMENTS);
		$HEAD .= sprintf("%02X", $this->_CURRENT);
		
		return $HEAD;
	}
}