<?php

require_once dirname(__FILE__).'/../src/OpenWebNet.php';

$own = new OpenWebNetLight('192.168.1.40', 20000, '12345', OpenWebNetDebuggingLevel::VERBOSE);

$own_light = $own->GetLightInstance();

// Turn light 1.2 ON
$own_light->SetLight(OpenWebNetLocations::Address(1, 2), 0);

// 0
var_dump($own_light->GetLightStatus(OpenWebNetLocations::Address(1, 2)));

// Turn ON all lights in area 3 for 1 minute
$own_light->SetLightTimedON(OpenWebNetLocations::Address(3, null), 60);

// Array of statuses
var_dump($own_light->GetLightStatus('31'));


// Blink
$own_light->SetLightBlinking('31', 1);
// 22
var_dump($own_light->GetLightStatus('31'));

// Dimmer
$own_light->SetLightDimmerLevel('12', 20);
// 2
var_dump($own_light->GetLightStatus('12'));