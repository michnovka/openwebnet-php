<?php

require_once dirname(__FILE__).'/../src/OpenWebNet.php';

$own = new OpenWebNetLight('192.168.1.40', 20000, '12345', OpenWebNetDebuggingLevel::VERBOSE);

$own_light = $own->GetLightInstance();

// Turn light off
$own_light->SetLight('12', 0);
// 0
var_dump($own_light->GetLightStatus('12'));

// Turn ON for 1 minute
$own_light->SetLightTimedON('31', 60);
// 1
var_dump($own_light->GetLightStatus('31'));


// Blink
$own_light->SetLightBlinking('31', 1);
// 22
var_dump($own_light->GetLightStatus('31'));

// Dimmer
$own_light->SetLightDimmerLevel('12', 20);
// 2
var_dump($own_light->GetLightStatus('12'));