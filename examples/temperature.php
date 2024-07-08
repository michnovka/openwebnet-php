<?php

require dirname(__FILE__).'/../vendor/autoload.php';

use Michnovka\OpenWebNet\OpenWebNet;
use Michnovka\OpenWebNet\OpenWebNetDebuggingLevel;

$own = new OpenWebNet('192.168.1.40', 20000, '12345', OpenWebNetDebuggingLevel::VERBOSE);

$own_temperature = $own->getTemperatureInstance();

// get current temperature in zone 2
var_dump($own_temperature->getTemperature(2));
