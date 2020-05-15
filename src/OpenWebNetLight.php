<?php

require_once dirname(__FILE__).'/OpenWebNet.php';

class OpenWebNetLight extends OpenWebNet{

	public function __construct($ip, $port = 20000, $password = '12345', $debugging_level = OpenWebNetDebuggingLevel::NONE)
	{
		parent::__construct($ip, $port, $password, $debugging_level);
	}

	public function __destruct()
	{
		parent::__destruct();
	}

	/**
	 * @param int $light_id
	 * @return int|null returns null if unknown status, int otherwise
	 * @throws OpenWebNetException
	 */
	public function GetLightStatus($light_id){

		$reply = $this->SendRaw('*#1*'.$light_id.'##', 1024, true);

		return $this->ParseStatusReply($light_id, 1, $reply);

	}


	/**
	 * @param int $light_id
	 * @param bool $status
	 * @return bool
	 * @throws OpenWebNetException
	 */
	public function SetLight($light_id, $status){

		$status = $status ? '1' : '0';

		$reply = $this->SendRaw('*1*'.$status.'*'.$light_id.'##');

		if($reply == OpenWebNetConstants::ACK){
			return true;
		}else{
			return false;
		}

	}

	/**
	 * @param int $light_id
	 * @param float $seconds
	 * @return bool
	 * @throws OpenWebNetException
	 */
	public function SetLightTimedON($light_id, $seconds){

		$allowed_seconds = [0.5 => 18, 60 => 11, 120 => 12, 180 => 13, 240 => 14, 300 => 15, 900 => 16, 1800 => 17];

		if(!array_key_exists($seconds, $allowed_seconds)){
			throw new OpenWebNetException("Time interval not supported: $seconds", OpenWebNetException::CODE_TIME_NOT_SUPPORTED);
		}

		$reply = $this->SendRaw('*1*'.$allowed_seconds[$seconds].'*'.$light_id.'##');

		if($reply == OpenWebNetConstants::ACK){
			return true;
		}else{
			return false;
		}

	}


	/**
	 * @param int $light_id
	 * @param float $seconds
	 * @return bool
	 * @throws OpenWebNetException
	 */
	public function SetLightBlinking($light_id, $seconds){

		$allowed_seconds = [0.5 => 20, 1 => 21, 1.5 => 22, 2 => 23, 2.5 => 24, 3 => 25, 3.5 => 26, 4 => 27, 4.5 => 28, 5 => 29];

		if(!array_key_exists($seconds, $allowed_seconds)){
			throw new OpenWebNetException("Time interval not supported: $seconds", OpenWebNetException::CODE_TIME_NOT_SUPPORTED);
		}

		$reply = $this->SendRaw('*1*'.$allowed_seconds[$seconds].'*'.$light_id.'##');

		if($reply == OpenWebNetConstants::ACK){
			return true;
		}else{
			return false;
		}

	}

	/**
	 * @param int $light_id
	 * @param int $dimmer_level
	 * @return bool
	 * @throws OpenWebNetException
	 */
	public function SetLightDimmerLevel($light_id, $dimmer_level){

		$allowed_levels = [0 => 0, 20 => 2, 30 => 3, 40 => 4, 50 => 5, 60 => 6, 70 => 7, 80 => 8, 90 => 9, 100 => 10];

		if(!array_key_exists($dimmer_level, $allowed_levels)){
			throw new OpenWebNetException("Dimmer level not supported: $dimmer_level", OpenWebNetException::CODE_DIMMER_LEVEL_NOT_SUPPORTED);
		}

		$reply = $this->SendRaw('*1*'.$allowed_levels[$dimmer_level].'*'.$light_id.'##');

		if($reply == OpenWebNetConstants::ACK){
			return true;
		}else{
			return false;
		}

	}

}