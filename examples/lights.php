<?php

require_once dirname(__FILE__).'/../src/OpenWebNet.php';

$own = new OpenWebNet('192.168.1.40', 20000, '12345', OpenWebNetDebuggingLevel::VERBOSE);

// turn on light ID 1.2
$own->Light('12', 0);