<?php

require_once dirname(__FILE__).'/OpenWebNetConstants.php';
require_once dirname(__FILE__) . '/OpenWebNetDebugging.php';
require_once dirname(__FILE__) . '/OpenWebNetLight.php';
require_once dirname(__FILE__) . '/OpenWebNetAutomation.php';
require_once dirname(__FILE__) . '/OpenWebNetScenario.php';
require_once dirname(__FILE__) . '/OpenWebNetTemperature.php';
require_once dirname(__FILE__) . '/OpenWebNetDoorLock.php';
require_once dirname(__FILE__).'/libs/OPENHash.php';
require_once dirname(__FILE__).'/libs/OpenWebNetLocations.php';

class OpenWebNetException extends Exception{

	public const CODE_CANNOT_CONNECT = 1;
	public const CODE_WRONG_REPLY = 2;
	public const CODE_AUTHENTICATION_ERROR = 3;
	public const CODE_NO_REPLY = 4;
	public const CODE_TIME_NOT_SUPPORTED = 5;
	public const CODE_DIMMER_LEVEL_NOT_SUPPORTED = 6;

	public function __construct($message = "", $code = 0, Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);

		OpenWebNetDebugging::LogTime("Exception thrown: [$code] - $message", OpenWebNetDebuggingLevel::NORMAL);
	}
}


class OpenWebNet{

	/** @var string $ip */
	protected $ip;

	/** @var int $port */
	protected $port;

	/** @var string $password */
	protected $password;

	/** @var null|resource $socket */
	protected $socket = null;

	/** @var int $debugging_level */
	protected $debugging_level;

	/** @var OpenWebNetLight|null */
	private $module_instance_light;

	/** @var OpenWebNetAutomation|null */
	private $module_instance_automation;

	/** @var OpenWebNetDoorLock|null */
	private $module_instance_door_lock;

	/** @var OpenWebNetScenario|null */
	private $module_instance_scenario;

	/** @var OpenWebNetTemperature|null */
	private $module_instance_temperature;

	/**
	 * OpenWebNet constructor.
	 * @param string $ip
	 * @param int $port
	 * @param string $password
	 * @param int $debugging_level
	 */
	public function __construct($ip, $port = 20000, $password = '12345', $debugging_level = OpenWebNetDebuggingLevel::NONE)
	{

		$this->ip = $ip;
		$this->port = $port;
		$this->password = $password;
		$this->debugging_level = $debugging_level;

		OpenWebNetDebugging::SetDebuggingLevel($debugging_level);
	}

	/**
	 * @return bool
	 */
	protected function IsConnected(){
		return $this->socket ? true : false;
	}

	/**
	 * Close socket
	 */
	public function __destruct()
	{
		//$this->Disconnect();
	}

	/**
	 * Closes socket
	 */
	protected function Disconnect(){
		if($this->IsConnected()){
			OpenWebNetDebugging::LogTime("Closing connection to ".$this->ip.":".$this->port, OpenWebNetDebuggingLevel::NORMAL);
			fclose($this->socket);
		}
	}

	/**
	 * @return bool
	 * @throws OpenWebNetException
	 */
	protected function Connect(){
		if($this->IsConnected())
			return true;

		$error_number = null;
		$error_message = null;

		OpenWebNetDebugging::LogTime("Connecting to ".$this->ip.":".$this->port, OpenWebNetDebuggingLevel::NORMAL);

		//$this->socket = fsockopen($this->ip, $this->port, $error_number, $error_message, 5);

		$this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

		//socket_set_nonblock($this->socket);

		socket_connect($this->socket, $this->ip, $this->port);

		OpenWebNetDebugging::LogTime("Connected", OpenWebNetDebuggingLevel::VERBOSE);

		if(!$this->socket){
			$error_number = socket_last_error($this->socket);
			$error_message = socket_strerror($error_number);

			throw new OpenWebNetException("Error connecting to server: [$error_number] - $error_message", OpenWebNetException::CODE_CANNOT_CONNECT);
		}else{

			$answer = socket_read($this->socket,1024);

			OpenWebNetDebugging::LogTime("Received reply: ".$answer, OpenWebNetDebuggingLevel::VERBOSE);

			if($answer == OpenWebNetConstants::ACK){

				// now authenticate
				$message = OpenWebNetConstants::REQUEST_AUTH;

				OpenWebNetDebugging::LogTime("Requesting authentication with ".$message, OpenWebNetDebuggingLevel::VERBOSE);

				socket_write($this->socket, $message);

				$answer = socket_read($this->socket, 128);

				OpenWebNetDebugging::LogTime("Received reply: ".$answer, OpenWebNetDebuggingLevel::VERBOSE);

				$nonce = str_ireplace(['#', '*'], '', $answer);

				$hash = OPENHash::Calculate($this->password, $nonce);

				OpenWebNetDebugging::LogTime("Nonce: ".$nonce, OpenWebNetDebuggingLevel::VERBOSE);
				OpenWebNetDebugging::LogTime("Hash: ".$hash, OpenWebNetDebuggingLevel::VERBOSE);

				OpenWebNetDebugging::LogTime("Sending hash", OpenWebNetDebuggingLevel::VERBOSE);

				socket_write($this->socket,'*#'.$hash.'##');

				$answer = socket_read($this->socket,6);

				OpenWebNetDebugging::LogTime("Received reply: ".$answer, OpenWebNetDebuggingLevel::VERBOSE);

				if($answer == OpenWebNetConstants::ACK){
					OpenWebNetDebugging::LogTime("Authentication success", OpenWebNetDebuggingLevel::NORMAL);

					return true;
				}else{
					throw new OpenWebNetException("Authentication error", OpenWebNetException::CODE_AUTHENTICATION_ERROR);
				}

			}else{
				throw new OpenWebNetException("Did not receive ACK upon connecting to server", OpenWebNetException::CODE_WRONG_REPLY);
			}

		}
	}

	/**
	 * @param $message
	 * @param int $buffer
	 * @param bool $read_until_ack
	 * @return false|string
	 * @throws OpenWebNetException
	 */
	protected function SendRaw($message, $buffer = 1024, $read_until_ack = false){

		$this->Connect();

		OpenWebNetDebugging::LogTime("Sending message: ".$message, OpenWebNetDebuggingLevel::VERBOSE);

		socket_write($this->socket, $message);

		$answer = socket_read($this->socket,$buffer);

		if(!$answer){
			throw new OpenWebNetException("No reply from server", OpenWebNetException::CODE_NO_REPLY);
		}

		OpenWebNetDebugging::LogTime("Received reply: ".$answer, OpenWebNetDebuggingLevel::VERBOSE);

		if($read_until_ack){
			while(!preg_match('/^(.*)\*#\*1##$/i', $answer, $m)) {
				OpenWebNetDebugging::LogTime("No ACK received, reading again.", OpenWebNetDebuggingLevel::VERBOSE);
				$answer2 = socket_read($this->socket,1024);
				OpenWebNetDebugging::LogTime("Received reply: ".$answer2, OpenWebNetDebuggingLevel::VERBOSE);

				$answer .= $answer2;
			}

			OpenWebNetDebugging::LogTime("Received final ACK: ".$answer2, OpenWebNetDebuggingLevel::VERBOSE);
		}

		return $answer;

	}

	/**
	 * @param resource $socket
	 */
	public function SetSocket($socket){
		$this->socket = $socket;
	}

	/**
	 * @return OpenWebNetLight|null
	 * @throws OpenWebNetException
	 */
	public function GetLightInstance(){
		if(empty($this->module_instance_light)){
			$this->Connect();
			$this->module_instance_light = new OpenWebNetLight($this->ip, $this->port, $this->password, $this->debugging_level);
			$this->module_instance_light->SetSocket($this->socket);
		}

		return $this->module_instance_light;
	}


	/**
	 * @return OpenWebNetAutomation|null
	 * @throws OpenWebNetException
	 */
	public function GetAutomationInstance(){
		if(empty($this->module_instance_automation)){
			$this->Connect();
			$this->module_instance_automation = new OpenWebNetAutomation($this->ip, $this->port, $this->password, $this->debugging_level);
			$this->module_instance_automation->SetSocket($this->socket);
		}

		return $this->module_instance_automation;
	}


	/**
	 * @return OpenWebNetScenario|null
	 * @throws OpenWebNetException
	 */
	public function GetScenarioInstance(){
		if(empty($this->module_instance_scenario)){
			$this->Connect();
			$this->module_instance_scenario = new OpenWebNetScenario($this->ip, $this->port, $this->password, $this->debugging_level);
			$this->module_instance_scenario->SetSocket($this->socket);
		}

		return $this->module_instance_scenario;
	}

	/**
	 * @return OpenWebNetTemperature|null
	 * @throws OpenWebNetException
	 */
	public function GetTemperatureInstance(){
		if(empty($this->module_instance_temperature)){
			$this->Connect();
			$this->module_instance_temperature = new OpenWebNetTemperature($this->ip, $this->port, $this->password, $this->debugging_level);
			$this->module_instance_temperature->SetSocket($this->socket);
		}

		return $this->module_instance_temperature;
	}

	/**
	 * @return OpenWebNetDoorLock|null
	 * @throws OpenWebNetException
	 */
	public function GetDoorLockInstance(){
		if(empty($this->module_instance_door_lock)){
			$this->Connect();
			$this->module_instance_door_lock = new OpenWebNetDoorLock($this->ip, $this->port, $this->password, $this->debugging_level);
			$this->module_instance_door_lock->SetSocket($this->socket);
		}

		return $this->module_instance_door_lock;
	}


	protected function ParseStatusReply($address, $who, $reply){

		if(OpenWebNetLocations::IsArea($address)){

			$results = array();
			$replies = explode('##',$reply);

			foreach($replies as $r){
				if($r == '*#*1')
					break;

				if(preg_match('/^\*'.$who.'\*([0-9]+)\*([0-9]+)$/i', $r, $m)){
					$results[$m[2]] = $m[1];
				}
			}

			return $results;
		}else{
			if(preg_match('/^\*'.$who.'\*([0-9]+)\*'.$address.'##/i', $reply, $m)){
				return $m[1];
			}else{
				return null;
			}
		}
	}

}