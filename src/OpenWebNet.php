<?php

declare(strict_types=1);

namespace Michnovka\OpenWebNet;

use Socket;

class OpenWebNet
{
    protected string $ip;

    protected int $port;

    protected string $password;

    protected Socket $socket;

    protected OpenWebNetDebuggingLevel $debuggingLevel;

    private ?OpenWebNetLight $module_instance_light;

    private ?OpenWebNetAutomation $module_instance_automation;

    private ?OpenWebNetDoorLock $module_instance_door_lock;

    private ?OpenWebNetScenario $module_instance_scenario;

    private ?OpenWebNetTemperature $module_instance_temperature;

    public function __construct(
        string $ip,
        int $port = 20000,
        string $password = '12345',
        OpenWebNetDebuggingLevel $debuggingLevel = OpenWebNetDebuggingLevel::NONE,
    ) {

        $this->ip = $ip;
        $this->port = $port;
        $this->password = $password;
        $this->debuggingLevel = $debuggingLevel;

        OpenWebNetDebugging::setDebuggingLevel($debuggingLevel);
    }

    public function setSocket(Socket $socket): void
    {
        $this->socket = $socket;
    }

    /**
     * @throws OpenWebNetException
     */
    public function getLightInstance(): ?OpenWebNetLight
    {
        if (empty($this->module_instance_light)) {
            $this->connect();
            $this->module_instance_light = new OpenWebNetLight($this->ip, $this->port, $this->password, $this->debuggingLevel);
            $this->module_instance_light->setSocket($this->socket);
        }

        return $this->module_instance_light;
    }


    /**
     * @throws OpenWebNetException
     */
    public function getAutomationInstance(): ?OpenWebNetAutomation
    {
        if (empty($this->module_instance_automation)) {
            $this->connect();
                $this->module_instance_automation = new OpenWebNetAutomation($this->ip, $this->port, $this->password, $this->debuggingLevel);
                $this->module_instance_automation->setSocket($this->socket);

        }

        return $this->module_instance_automation;
    }


    /**
     * @throws OpenWebNetException
     */
    public function getScenarioInstance(): ?OpenWebNetScenario
    {
        if (empty($this->module_instance_scenario)) {
            $this->connect();
                $this->module_instance_scenario = new OpenWebNetScenario($this->ip, $this->port, $this->password, $this->debuggingLevel);
                $this->module_instance_scenario->setSocket($this->socket);

        }

        return $this->module_instance_scenario;
    }

    /**
     * @throws OpenWebNetException
     */
    public function getTemperatureInstance(): ?OpenWebNetTemperature
    {
        if (empty($this->module_instance_temperature)) {
            $this->connect();
                $this->module_instance_temperature = new OpenWebNetTemperature($this->ip, $this->port, $this->password, $this->debuggingLevel);
                $this->module_instance_temperature->setSocket($this->socket);

        }

        return $this->module_instance_temperature;
    }

    /**
     * @throws OpenWebNetException
     */
    public function getDoorLockInstance(): ?OpenWebNetDoorLock
    {
        if (empty($this->module_instance_door_lock)) {
            $this->connect();
                $this->module_instance_door_lock = new OpenWebNetDoorLock($this->ip, $this->port, $this->password, $this->debuggingLevel);
                $this->module_instance_door_lock->setSocket($this->socket);

        }

        return $this->module_instance_door_lock;
    }

    /**
     */
    protected function isConnected(): bool
    {
        return (bool) $this->socket;
    }

    /**
     * Closes socket
     */
    protected function disconnect(): void
    {
        OpenWebNetDebugging::logTime("Closing connection to " . $this->ip . ":" . $this->port, OpenWebNetDebuggingLevel::NORMAL);
        socket_close($this->socket);
        unset($this->socket);
    }

    /**
     * Check if the socket is active
     */
    public function isSocketActive(): bool {
        return isset($this->socket);
    }

    /**
     * @throws OpenWebNetException
     */
    protected function connect(): bool
    {
        if ($this->isSocketActive()) {
            return true;
        }

        $errorNumber = null;
        $errorMessage = null;

        OpenWebNetDebugging::logTime("Connecting to " . $this->ip . ":" . $this->port, OpenWebNetDebuggingLevel::NORMAL);

        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

        if ($socket === false) {
            $errorNumber = socket_last_error();
            $errorMessage = socket_strerror($errorNumber);
            throw new OpenWebNetException("Error connecting to server: [$errorNumber] - $errorMessage", OpenWebNetException::CODE_CANNOT_CONNECT);
        }else{
            $this->socket = $socket;
        }

        $connect_result = socket_connect($this->socket, $this->ip, $this->port);

        OpenWebNetDebugging::logTime("Connected", OpenWebNetDebuggingLevel::VERBOSE);

        if ($connect_result === false) {
            $errorNumber = socket_last_error($this->socket);
            $errorMessage = socket_strerror($errorNumber);

            throw new OpenWebNetException("Error connecting to server: [$errorNumber] - $errorMessage", OpenWebNetException::CODE_CANNOT_CONNECT);
        } else {
            $answer = socket_read($this->socket, 1024);

            OpenWebNetDebugging::logTime("Received reply: " . $answer, OpenWebNetDebuggingLevel::VERBOSE);

            if ($answer == OpenWebNetConstants::ACK) {
                // now authenticate
                $message = OpenWebNetConstants::REQUEST_AUTH;

                OpenWebNetDebugging::logTime("Requesting authentication with " . $message, OpenWebNetDebuggingLevel::VERBOSE);

                socket_write($this->socket, $message);

                $answer = socket_read($this->socket, 128);

                OpenWebNetDebugging::logTime("Received reply: " . $answer, OpenWebNetDebuggingLevel::VERBOSE);

                $nonce = str_ireplace(['#', '*'], '', $answer);

                $hash = OPENHash::calculate($this->password, $nonce);

                OpenWebNetDebugging::logTime("Nonce: " . $nonce, OpenWebNetDebuggingLevel::VERBOSE);
                OpenWebNetDebugging::logTime("Hash: " . $hash, OpenWebNetDebuggingLevel::VERBOSE);

                OpenWebNetDebugging::logTime("Sending hash", OpenWebNetDebuggingLevel::VERBOSE);

                socket_write($this->socket, '*#' . $hash . '##');

                $answer = socket_read($this->socket, 6);

                OpenWebNetDebugging::logTime("Received reply: " . $answer, OpenWebNetDebuggingLevel::VERBOSE);

                if ($answer == OpenWebNetConstants::ACK) {
                    OpenWebNetDebugging::logTime("Authentication success", OpenWebNetDebuggingLevel::NORMAL);

                    return true;
                } else {
                    throw new OpenWebNetException("Authentication error", OpenWebNetException::CODE_AUTHENTICATION_ERROR);
                }
            } else {
                throw new OpenWebNetException("Did not receive ACK upon connecting to server", OpenWebNetException::CODE_WRONG_REPLY);
            }
        }
    }

    /**
     * @throws OpenWebNetException
     */
    protected function sendRaw(string $message, int $buffer = 1024, bool $readUntilACK = false): string
    {

        $this->connect();

        OpenWebNetDebugging::logTime("Sending message: " . $message, OpenWebNetDebuggingLevel::VERBOSE);

        socket_write($this->socket, $message);

        $answer = socket_read($this->socket, $buffer);

        if (!$answer) {
            throw new OpenWebNetException("No reply from server", OpenWebNetException::CODE_NO_REPLY);
        }

        OpenWebNetDebugging::logTime("Received reply: " . $answer, OpenWebNetDebuggingLevel::VERBOSE);

        if ($readUntilACK) {
            while (!preg_match('/^(.*)\*#\*1##$/i', $answer, $m)) {
                OpenWebNetDebugging::logTime("No ACK received, reading again.", OpenWebNetDebuggingLevel::VERBOSE);
                $answer2 = socket_read($this->socket, 1024);
                OpenWebNetDebugging::logTime("Received reply: " . $answer2, OpenWebNetDebuggingLevel::VERBOSE);

                $answer .= $answer2;
            }

            OpenWebNetDebugging::logTime("Received final ACK: " . $answer, OpenWebNetDebuggingLevel::VERBOSE);
        }

        return $answer;
    }


    /**
     * @return array<int,int>|int|null
     */
    protected function parseStatusReply(string $address, int $who, string $reply): array|int|null
    {

        if (OpenWebNetLocations::isArea($address)) {
            $results = [];
            $replies = explode('##', $reply);

            foreach ($replies as $r) {
                if ($r == '*#*1') {
                    break;
                }

                if (preg_match('/^\*' . $who . '\*([0-9]+)\*([0-9]+)$/i', $r, $m)) {
                    $results[(int)$m[2]] = (int) $m[1];
                }
            }

            return $results;
        } else {
            if (preg_match('/^\*' . $who . '\*([0-9]+)\*' . $address . '##/i', $reply, $m)) {
                return (int)$m[1];
            } else {
                return null;
            }
        }
    }
}
