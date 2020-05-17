<?php

require_once dirname(__FILE__).'/OpenWebNet.php';

class OpenWebNetTemperature extends OpenWebNet{

	public function __construct($ip, $port = 20000, $password = '12345', $debugging_level = OpenWebNetDebuggingLevel::NONE)
	{
		parent::__construct($ip, $port, $password, $debugging_level);
	}

	public function __destruct()
	{
		parent::__destruct();
	}

	/**
	 * @param int $zone_id
	 * @return float|null
	 * @throws OpenWebNetException
	 */
	public function GetTemperature($zone_id){
		$reply = $this->SendRaw('*#4*'.$zone_id.'*0##', 1024, true);

		if(preg_match('/\*#4\*'.$zone_id.'\*0\*([0-9]+)##/i', $reply, $m)){
			return $this->OPENTempToFloat($m[1]);
		}else{
			return null;
		}
	}

	public function OPENTempToFloat($open_temp){
		$sign = substr($open_temp, 0 , 1) == 0 ? 1 : (-1);

		$temp = floatval(substr($open_temp, 1,3))/10 * $sign;

		return $temp;
	}


}