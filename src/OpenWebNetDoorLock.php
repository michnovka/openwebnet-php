<?php

declare(strict_types=1);

namespace Michnovka\OpenWebNet;

class OpenWebNetDoorLock extends OpenWebNet
{
    /**
     * @throws OpenWebNetException
     */
    public function openDoor(int $doorId): bool
    {
        $doorId += 4000;

        $message = '*6*10*' . $doorId . '##';

        $reply = $this->sendRaw($message);

        if ($reply == OpenWebNetConstants::ACK) {
            return true;
        } else {
            return false;
        }
    }
}
