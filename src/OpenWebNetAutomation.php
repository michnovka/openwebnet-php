<?php

declare(strict_types=1);

namespace Michnovka\OpenWebNet;

class OpenWebNetAutomation extends OpenWebNet
{
    /**
     * @param string $automationAddress either a whole area or an individual automation
     * @return array<int,int>|int|null returns null if unknown status, int otherwise or an array if area was passed in address
     * @throws OpenWebNetException
     */
    public function getAutomationStatus(string $automationAddress): array|int|null
    {

        $reply = $this->sendRaw('*#2*' . $automationAddress . '##', 1024, true);

        return $this->parseStatusReply($automationAddress, 2, $reply);
    }


    /**
     * @param int $status 0 - stop, 1 - up, 2 - down
     * @throws OpenWebNetException
     */
    public function setBasicActuator(string $automationId, int $status): bool
    {

        $currentStatus = $this->getAutomationStatus($automationId);

        if ($currentStatus != 0) {
            $this->sendRaw('*2*0*' . $automationId . '##');
        }

        $reply = $this->sendRaw('*2*' . $status . '*' . $automationId . '##');

        if ($reply == OpenWebNetConstants::ACK) {
            return true;
        } else {
            return false;
        }
    }
}
