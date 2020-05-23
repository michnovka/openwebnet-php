<?php

class OpenWebNetDebugging{

	protected static $current_debugging_level = OpenWebNetDebuggingLevel::NONE;

	/** @var null|string $output Null for console or file path for logging into files */
	protected static $output = null;

	/**
	 * @param int $debugging_level
	 */
	public static function SetDebuggingLevel($debugging_level){
		self::$current_debugging_level = $debugging_level;
	}

	/**
	 * @param string $message
	 * @param int $debugging_level
	 */
	static function Log($message, $debugging_level = OpenWebNetDebuggingLevel::NORMAL){
		if($debugging_level <= self::$current_debugging_level){
			if(self::$output === null){
				echo $message."\n";
			}else{
				file_put_contents(self::$output, $message."\n", FILE_APPEND);
			}
		}
	}

	/**
	 * @param string $message
	 * @param int $debugging_level
	 */
	static function LogTime($message, $debugging_level = OpenWebNetDebuggingLevel::NORMAL){
		return self::Log(date('Y-m-d H:i:s')." | ".$message, $debugging_level);
	}
}

class OpenWebNetDebuggingLevel
{
	const NONE = 0;
	const NORMAL = 1;
	const VERBOSE = 2;
}