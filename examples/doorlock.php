<?php

require dirname(__FILE__).'/../vendor/autoload.php';

use Michnovka\OpenWebNet\OpenWebNet;
use Michnovka\OpenWebNet\OpenWebNetDebuggingLevel;

$own = new OpenWebNet('192.168.1.40', 20000, '12345', OpenWebNetDebuggingLevel::VERBOSE);

$own_doorlock = $own->getDoorLockInstance();

// Open door 3
$own_doorlock->OpenDoor(2);

for($i = 0; $i< 100; $i++){
	$result = $own_doorlock->OpenDoor($i);
	echo $i." - ".($result ? 'OK' : 'FALSE')."\n";
}

