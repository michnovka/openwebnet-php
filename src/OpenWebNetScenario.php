<?php

declare(strict_types=1);

namespace Michnovka\OpenWebNet;

class OpenWebNetScenario extends OpenWebNet
{
    /**
     * @throws OpenWebNetException
     */
    public function virtualPress(string $scenarioId, string $buttonId): bool
    {
        return $this->executeAction($buttonId, $scenarioId, null);
    }

    /**
     * @throws OpenWebNetException
     */
    protected function executeAction(string $what, string $where, ?string $subAction = null): bool
    {

        $message = '*15*' . $what;

        if ($subAction) {
            $message .= '*' . $subAction;
        }

        $message .= '*' . $where . '##';

        $reply = $this->sendRaw($message);

        if ($reply == OpenWebNetConstants::ACK) {
            return true;
        } else {
            return false;
        }
    }
}
