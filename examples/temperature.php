<?php

require_once dirname(__FILE__).'/../src/OpenWebNet.php';

$own = new OpenWebNet('192.168.1.40', 20000, '12345', OpenWebNetDebuggingLevel::VERBOSE);

$own = $own->GetTemperatureInstance();

// get current temperature in zone 2
var_dump($own->GetTemperature(2));
