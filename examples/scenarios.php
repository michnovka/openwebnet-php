<?php

require_once dirname(__FILE__).'/../src/OpenWebNet.php';

$own = new OpenWebNet('192.168.1.40', 20000, '12345', OpenWebNetDebuggingLevel::VERBOSE);

$own = $own->GetScenarioInstance();

// press button 01 on scenario 10.1
$own->VirtualPressure(OpenWebNetLocations::Address(10, 1), '01');
