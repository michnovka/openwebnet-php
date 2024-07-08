<?php

declare(strict_types=1);

namespace Michnovka\OpenWebNet;

class OpenWebNetTemperature extends OpenWebNet
{
    /**
     * @throws OpenWebNetException
     */
    public function getTemperature(int $zoneId): ?float
    {
        $reply = $this->sendRaw('*#4*' . $zoneId . '*0##', 1024, true);

        if (preg_match('/\*#4\*' . $zoneId . '\*0\*([0-9]+)##/i', $reply, $m)) {
            return $this->OPENTempToFloat($m[1]);
        } else {
            return null;
        }
    }

    public function OPENTempToFloat(string $openTemp): float
    {
        $sign = substr($openTemp, 0, 1) == 0 ? 1 : (-1);

        return floatval(substr($openTemp, 1, 3)) / 10 * $sign;
    }
}
