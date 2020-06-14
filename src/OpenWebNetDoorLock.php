<?php

require_once dirname(__FILE__).'/OpenWebNet.php';

class OpenWebNetDoorLock extends OpenWebNet{

	public function __construct($ip, $port = 20000, $password = '12345', $debugging_level = OpenWebNetDebuggingLevel::NONE)
	{
		parent::__construct($ip, $port, $password, $debugging_level);
	}

	public function __destruct()
	{
		parent::__destruct();
	}

	/**
	 * @param int $door_id
	 * @return bool
	 * @throws OpenWebNetException
	 */
	public function OpenDoor($door_id){

		$door_id += 4000;

		$message = '*6*10*'.$door_id.'##';

		$reply = $this->SendRaw($message);

		if($reply == OpenWebNetConstants::ACK){
			return true;
		}else{
			return false;
		}
	}


}