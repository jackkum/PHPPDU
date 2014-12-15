PHPPDU
======

Creating a PDU string for sending sms

# Usage
----------------------

```
require_once 'Submit.php';

$pdu = new Submit();

$pdu->setAddress("*********");
$pdu->setData("Message to sent.");

foreach($pdu->getParts() as $part){
	echo get_class($part), PHP_EOL;
	echo (string) $part, PHP_EOL;
}
```
