<?php

declare(strict_types=1);

namespace Michnovka\OpenWebNet;

class OpenWebNetDebugging
{
    protected static OpenWebNetDebuggingLevel $current_debugging_level = OpenWebNetDebuggingLevel::NONE;

    /** @var null|string $output Null for console or file path for logging into files */
    protected static ?string $output = null;

    public static function setDebuggingLevel(OpenWebNetDebuggingLevel $debuggingLevel): void
    {
        self::$current_debugging_level = $debuggingLevel;
    }

    public static function log(
        string $message,
        OpenWebNetDebuggingLevel $debuggingLevel = OpenWebNetDebuggingLevel::NORMAL,
    ): void {
        if ($debuggingLevel->value <= self::$current_debugging_level->value) {
            if (self::$output === null) {
                echo $message . "\n";
            } else {
                file_put_contents(self::$output, $message . "\n", FILE_APPEND);
            }
        }
    }

    public static function logTime(
        string $message,
        OpenWebNetDebuggingLevel $debuggingLevel = OpenWebNetDebuggingLevel::NORMAL,
    ): void {
        self::log(date('Y-m-d H:i:s') . " | " . $message, $debuggingLevel);
    }
}
