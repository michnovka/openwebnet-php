<?php

require_once dirname(__FILE__).'/../src/OpenWebNet.php';

$own = new OpenWebNetLight('192.168.1.40', 20000, '12345', OpenWebNetDebuggingLevel::VERBOSE);

$own_automation = $own->GetDoorLockInstance();

// Open door 3
$own_automation->OpenDoor(3);

