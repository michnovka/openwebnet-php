<?php

require_once dirname(__FILE__).'/../src/OpenWebNet.php';

$own = new OpenWebNetLight('192.168.1.40', 20000, '12345', OpenWebNetDebuggingLevel::VERBOSE);

$own_automation = $own->GetAutomationInstance();

// Check status of Shutters
var_dump($own_automation->GetAutomationStatus(OpenWebNetLocations::Address(8,8)));

// Roll shutters 8.8 down
$own_automation->SetBasicActuator(OpenWebNetLocations::Address(8,8), 2);


// Roll ALL shutters up
$own_automation->SetBasicActuator(OpenWebNetLocations::All(), 2);

