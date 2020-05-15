<?php

require_once dirname(__FILE__).'/../src/OpenWebNet.php';

$own = new OpenWebNetLight('192.168.1.40', 20000, '12345', OpenWebNetDebuggingLevel::VERBOSE);

$own_automation = $own->GetAutomationInstance();

// Check status of Shutters
var_dump($own_automation->GetAutomationStatus(OpenWebNetLocations::Address(8,8)));

// Roll shutters down
$own_automation->SetBasicActuator(OpenWebNetLocations::Address(8,8), 2);


