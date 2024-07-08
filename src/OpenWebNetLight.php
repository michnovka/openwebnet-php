<?php

declare(strict_types=1);

namespace Michnovka\OpenWebNet;

class OpenWebNetLight extends OpenWebNet
{
    /**
     * @param string $lightAddress either a whole are or an individual light
     * @return array<int,int>|int|null returns null if unknown status, int otherwise or an array if area was passed in address
     * @throws OpenWebNetException
     */
    public function getLightStatus(string $lightAddress): int|array|null
    {

        $reply = $this->sendRaw('*#1*' . $lightAddress . '##', 1024, true);

        return $this->parseStatusReply($lightAddress, 1, $reply);
    }

    /**
     * @throws OpenWebNetException
     */
    public function setLight(string $lightId, bool $status): bool
    {
        $reply = $this->sendRaw('*1*' . ($status ? '1' : '0') . '*' . $lightId . '##');

        if ($reply == OpenWebNetConstants::ACK) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @throws OpenWebNetException
     */
    public function setLightTimedON(string $lightId, float|int $seconds): bool
    {
        if ($seconds == 0.5) {
            $command = 18;
        } else {
            $command = match ((int) $seconds) {
                60 => 11,
                120 => 12,
                180 => 13,
                240 => 14,
                300 => 15,
                900 => 16,
                1800 => 17,
                default => throw new OpenWebNetException("Time interval not supported: $seconds", OpenWebNetException::CODE_TIME_NOT_SUPPORTED),
            };
        }

        $reply = $this->sendRaw('*1*' . $command . '*' . $lightId . '##');

        if ($reply == OpenWebNetConstants::ACK) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * @throws OpenWebNetException
     */
    public function setLightBlinking(string $lightId, float $seconds): bool
    {
        $command = match ($seconds) {
            0.5 => 20,
            1.0 => 21,
            1.5 => 22,
            2.0 => 23,
            2.5 => 24,
            3.0 => 25,
            4.0 => 27,
            4.5 => 28,
            5.0 => 29,
            default => throw new OpenWebNetException("Time interval not supported: $seconds", OpenWebNetException::CODE_TIME_NOT_SUPPORTED),
        };

        $reply = $this->sendRaw('*1*' . $command . '*' . $lightId . '##');

        if ($reply == OpenWebNetConstants::ACK) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @throws OpenWebNetException
     */
    public function setLightDimmerLevel(string $lightId, int $dimmerLevel): bool
    {

        $allowedLevels = [0 => 0, 20 => 2, 30 => 3, 40 => 4, 50 => 5, 60 => 6, 70 => 7, 80 => 8, 90 => 9, 100 => 10];

        if (!array_key_exists($dimmerLevel, $allowedLevels)) {
            throw new OpenWebNetException("Dimmer level not supported: $dimmerLevel", OpenWebNetException::CODE_DIMMER_LEVEL_NOT_SUPPORTED);
        }

        $reply = $this->sendRaw('*1*' . $allowedLevels[$dimmerLevel] . '*' . $lightId . '##');

        if ($reply == OpenWebNetConstants::ACK) {
            return true;
        } else {
            return false;
        }
    }
}
