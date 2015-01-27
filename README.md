PHPPDU
======
[![Build Status](https://travis-ci.org/jackkum/PHPPDU.svg?branch=master)](https://travis-ci.org/jackkum/PHPPDU)

Creating a PDU string for sending sms

# Usage
----------------------

```
require_once 'PDU.php';

$pdu = new Submit();

$pdu->setAddress("*********");
$pdu->setData("Message to sent.");

foreach($pdu->getParts() as $part){
	echo get_class($part), PHP_EOL;
	echo (string) $part, PHP_EOL;
}
```
----------------------

```
$pdu = PDU::parse($str);

echo $pdu->getAddress()->getPhone(), PHP_EOL;

foreach($pdu->getParts() as $part){
	$header = $part->getHeader();
	echo "unique: ", $header->getPointer(), PHP_EOL;
	echo $header->getCurrent(), " of ", $header->getSegments(), PHP_EOL;
	echo $tmp->getData()->getData(), PHP_EOL;
}

```
----------------------

# TODO
 - Merge parsed messages
 - Report status pdu type
