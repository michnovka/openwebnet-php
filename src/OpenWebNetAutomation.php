<?php

require_once dirname(__FILE__).'/OpenWebNet.php';

class OpenWebNetAutomation extends OpenWebNet{

	public function __construct($ip, $port = 20000, $password = '12345', $debugging_level = OpenWebNetDebuggingLevel::NONE)
	{
		parent::__construct($ip, $port, $password, $debugging_level);
	}

	public function __destruct()
	{
		parent::__destruct();
	}

	/**
	 * @param int $automation_id
	 * @return int|null returns null if unknown status, int otherwise
	 * @throws OpenWebNetException
	 */
	public function GetAutomationStatus($automation_id){

		$reply = $this->SendRaw('*#2*'.$automation_id.'##', 1024, true);

		if(preg_match('/^\*2\*([0-9]+)\*'.$automation_id.'##$/i', $reply, $m)){
			return $m[1];
		}else{
			return null;
		}

	}


	/**
	 * @param int $automation_id
	 * @param int $status 0 - stop, 1 - up, 2 - down
	 * @return bool
	 * @throws OpenWebNetException
	 */
	public function SetBasicActuator($automation_id, $status){

		$current_status = $this->GetAutomationStatus($automation_id);

		if($current_status != 0){
			$this->SendRaw('*2*0*'.$automation_id.'##');
		}

		$reply = $this->SendRaw('*2*'.$status.'*'.$automation_id.'##');

		if($reply == OpenWebNetConstants::ACK){
			return true;
		}else{
			return false;
		}

	}


}