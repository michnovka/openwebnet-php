<?php

require_once dirname(__FILE__).'/../src/OpenWebNetLight.php';

$own = new OpenWebNetLight('192.168.1.40', 20000, '12345', OpenWebNetDebuggingLevel::VERBOSE);

// Turn light off
$own->SetLight('12', 0);
// 0
var_dump($own->GetLightStatus('12'));

// Turn ON for 1 minute
$own->SetLightTimedON('31', 60);
// 1
var_dump($own->GetLightStatus('31'));


// Blink
$own->SetLightBlinking('31', 1);
// 22
var_dump($own->GetLightStatus('31'));

// Dimmer
$own->SetLightDimmerLevel('12', 20);
// 2
var_dump($own->GetLightStatus('12'));