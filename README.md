# PHPPDU [![Build Status](https://travis-ci.org/jackkum/PHPPDU.svg?branch=master)](https://travis-ci.org/jackkum/PHPPDU) [![Code Climate](https://codeclimate.com/github/jackkum/PHPPDU/badges/gpa.svg)](https://codeclimate.com/github/jackkum/PHPPDU)

Creating a PDU string for sending sms

# Usage

composer require jackkum/phppdu

----------------------

```php
require_once './vendor/autoload.php';

use jackkum\PHPPDU\Submit;

$pdu = new Submit();

$pdu->setAddress("*********");
$pdu->setData("Message to sent.");

foreach($pdu->getParts() as $part){
	echo get_class($part), PHP_EOL;
	echo (string) $part, PHP_EOL;
}
```
----------------------

```php
require_once './vendor/autoload.php';

use jackkum\PHPPDU\PDU;

$pdu = PDU::parse($str);

echo $pdu->getAddress()->getPhone(), PHP_EOL;

foreach($pdu->getParts() as $part){
	$header = $part->getHeader();
	echo "unique: ", $header->getPointer(), PHP_EOL;
	echo $header->getCurrent(), " of ", $header->getSegments(), PHP_EOL;
}

echo $tmp->getData()->getData(), PHP_EOL;

```
----------------------

```php
require_once './vendor/autoload.php';

use jackkum\PHPPDU\PDU;

$main = NULL;

foreach(self::$lines as $line){
	if(is_null($main)){
		$main = PDU::parse($line);
	} else {
		$main->getData()->append(
			PDU::parse($line)
		);
	}
	
}

echo $main->getData()->getData(), PHP_EOL;
```
