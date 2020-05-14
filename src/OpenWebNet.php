<?php

require_once dirname(__FILE__).'/OpenWebNetConstants.php';
require_once dirname(__FILE__) . '/OpenWebNetDebugging.php';
require_once dirname(__FILE__) . '/OpenWebNetLight.php';
require_once dirname(__FILE__) . '/OpenWebNetAutomation.php';
require_once dirname(__FILE__).'/libs/OPENHash.php';

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
	private ?OpenWebNetLight $module_instance_light;

	/** @var OpenWebNetAutomation|null */
	private ?OpenWebNetAutomation $module_instance_automation;

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

		$this->socket = fsockopen($this->ip, $this->port, $error_number, $error_message, 5);

		OpenWebNetDebugging::LogTime("Connected", OpenWebNetDebuggingLevel::VERBOSE);

		if(!$this->socket){
			throw new OpenWebNetException("Error connecting to server: [$error_number] - $error_message", OpenWebNetException::CODE_CANNOT_CONNECT);
		}else{

			$answer = fread($this->socket,7);

			OpenWebNetDebugging::LogTime("Received reply: ".$answer, OpenWebNetDebuggingLevel::VERBOSE);

			if($answer == OpenWebNetConstants::ACK){

				// now authenticate
				$message = OpenWebNetConstants::REQUEST_AUTH;

				OpenWebNetDebugging::LogTime("Requesting authentication with ".$message, OpenWebNetDebuggingLevel::VERBOSE);

				fwrite($this->socket, $message);

				$answer = fread($this->socket, 128);

				OpenWebNetDebugging::LogTime("Received reply: ".$answer, OpenWebNetDebuggingLevel::VERBOSE);

				$nonce = str_ireplace(['#', '*'], '', $answer);

				$hash = OPENHash::Calculate($this->password, $nonce);

				OpenWebNetDebugging::LogTime("Nonce: ".$nonce, OpenWebNetDebuggingLevel::VERBOSE);
				OpenWebNetDebugging::LogTime("Hash: ".$hash, OpenWebNetDebuggingLevel::VERBOSE);

				OpenWebNetDebugging::LogTime("Sending hash", OpenWebNetDebuggingLevel::VERBOSE);

				fwrite($this->socket,'*#'.$hash.'##');

				$answer = fread($this->socket,7);

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
	 * @param bool $read_final_ack
	 * @return false|string
	 * @throws OpenWebNetException
	 */
	protected function SendRaw($message, $buffer = 1024, $read_final_ack = false){

		$this->Connect();

		OpenWebNetDebugging::LogTime("Sending message: ".$message, OpenWebNetDebuggingLevel::VERBOSE);

		fwrite($this->socket, $message);

		$answer = fread($this->socket,$buffer);

		if($answer === false){
			throw new OpenWebNetException("No reply from server", OpenWebNetException::CODE_NO_REPLY);
		}

		OpenWebNetDebugging::LogTime("Received reply: ".$answer, OpenWebNetDebuggingLevel::VERBOSE);

		if($read_final_ack){

			$answer2 = null;

			if(!preg_match('/^(.*)\*#\*1##$/i', $answer, $m)) {
				$answer2 = fread($this->socket, 7);
			}else{
				$answer = $m[1];
				$answer2 = OpenWebNetConstants::ACK;
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



}