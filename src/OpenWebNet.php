<?php

require_once dirname(__FILE__).'/OpenWebNetConstants.php';
require_once dirname(__FILE__) . '/OpenWebNetDebugging.php';
require_once dirname(__FILE__).'/libs/OPENHash.php';

class OpenWebNetException extends Exception{

	public const CODE_CANNOT_CONNECT = 1;
	public const CODE_WRONG_REPLY = 2;
	public const CODE_AUTHENTICATION_ERROR = 3;
	public const CODE_NO_REPLY = 4;

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

		OpenWebNetDebugging::SetDebuggingLevel($debugging_level);
	}

	/**
	 * @return bool
	 */
	protected function IsConnected(){
		return $this->socket ? true : false;
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
	 * @return false|string
	 * @throws OpenWebNetException
	 */
	protected function SendRaw($message, $buffer = 1024){

		$this->Connect();

		OpenWebNetDebugging::LogTime("Sending message: ".$message, OpenWebNetDebuggingLevel::VERBOSE);

		fwrite($this->socket, $message);

		$answer = fread($this->socket,$buffer);

		if($answer === false){
			throw new OpenWebNetException("No reply from server", OpenWebNetException::CODE_NO_REPLY);
		}

		OpenWebNetDebugging::LogTime("Received reply: ".$answer, OpenWebNetDebuggingLevel::VERBOSE);

		return $answer;

	}

	public function Light($light_id, $status){

		$status = $status ? '1' : '0';

		$reply = $this->SendRaw('*1*'.$status.'*'.$light_id.'##');

		if($reply == OpenWebNetConstants::ACK){
			return true;
		}else{
			return false;
		}

	}


}