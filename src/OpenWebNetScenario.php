<?php

require_once dirname(__FILE__).'/OpenWebNet.php';

class OpenWebNetScenario extends OpenWebNet{

	public function __construct($ip, $port = 20000, $password = '12345', $debugging_level = OpenWebNetDebuggingLevel::NONE)
	{
		parent::__construct($ip, $port, $password, $debugging_level);
	}

	public function __destruct()
	{
		parent::__destruct();
	}

	/**
	 * @param int $what
	 * @param int $where
	 * @param null|int $sub_action
	 * @return bool
	 * @throws OpenWebNetException
	 */
	protected function ExecuteAction($what, $where, $sub_action = null){

		$message = '*15*'.$what;

		if($sub_action)
			$message .= '*'.$sub_action;

		$message .= '*'.$where.'##';

		$reply = $this->SendRaw($message);

		if($reply == OpenWebNetConstants::ACK){
			return true;
		}else{
			return false;
		}

	}

	/**
	 * @param $scenario_id
	 * @param $button_id
	 * @return bool
	 * @throws OpenWebNetException
	 */
	public function VirtualPressure($scenario_id, $button_id){
		return $this->ExecuteAction($button_id, $scenario_id, null);
	}


}