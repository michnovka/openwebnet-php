# ATTENTION! This is legacy repository that provides only source code without composer packaging and namespaces. Use master branch for most up to date version

# openwebnet-php
This library aims to wrap basic OPENWebNet functionality for Bticino products in PHP

## Requirements
- PHP 7.3+
- OPEN Gateway (tested with MH202, but any such as F454 or F420 should work)

## Supported Features
- Lights
- Door lock (open doors only)
- Automation (Basic actuator)
- Scenarios (Virtual button press)

## Known limitations (to be implemented)
- Automation only works with basic actuator
- Only fetching of temperature - no setting
- no more advanced Virtual button press types (short press, extended press, etc.)