<?php

require dirname(__FILE__).'/../vendor/autoload.php';

use Michnovka\OpenWebNet\OpenWebNet;
use Michnovka\OpenWebNet\OpenWebNetLocations;
use Michnovka\OpenWebNet\OpenWebNetDebuggingLevel;

$own = new OpenWebNet('192.168.1.40', 20000, '12345', OpenWebNetDebuggingLevel::VERBOSE);

$own_automation = $own->getAutomationInstance();

// Check status of Shutters
var_dump($own_automation->getAutomationStatus(OpenWebNetLocations::address(8,8)));

// Roll shutters 8.8 down
$own_automation->setBasicActuator(OpenWebNetLocations::address(8,8), 2);

// Roll ALL shutters up
$own_automation->setBasicActuator(OpenWebNetLocations::all(), 2);

