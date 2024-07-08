<?php

require dirname(__FILE__).'/../vendor/autoload.php';

use Michnovka\OpenWebNet\OpenWebNet;
use Michnovka\OpenWebNet\OpenWebNetDebuggingLevel;
use Michnovka\OpenWebNet\OpenWebNetLocations;

$own = new OpenWebNet('192.168.1.40', 20000, '12345', OpenWebNetDebuggingLevel::VERBOSE);

$own_scenario = $own->getScenarioInstance();

// press button 01 on scenario 10.1
$own_scenario->virtualPress(OpenWebNetLocations::address(10, 1), '01');
