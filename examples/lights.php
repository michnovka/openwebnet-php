<?php

require dirname(__FILE__).'/../vendor/autoload.php';

use Michnovka\OpenWebNet\OpenWebNet;
use Michnovka\OpenWebNet\OpenWebNetDebuggingLevel;
use Michnovka\OpenWebNet\OpenWebNetLocations;

$own = new OpenWebNet('192.168.1.40', 20000, '12345', OpenWebNetDebuggingLevel::VERBOSE);

$own_light = $own->getLightInstance();

// Turn light 1.2 ON
$own_light->setLight(OpenWebNetLocations::address(1, 2), 0);

// 0
var_dump($own_light->getLightStatus(OpenWebNetLocations::address(1, 2)));

// Turn ON all lights in area 3 for 1 minute
$own_light->setLightTimedON(OpenWebNetLocations::address(3, null), 60);

// Array of statuses
var_dump($own_light->getLightStatus('31'));


// Blink
$own_light->setLightBlinking('31', 1);
// 22
var_dump($own_light->getLightStatus('31'));

// Dimmer
$own_light->setLightDimmerLevel('12', 20);
// 2
var_dump($own_light->getLightStatus('12'));