<?php

declare(strict_types=1);

namespace Voucher\MessageQueue\RabbitMQ;

use Laminas\Log\LoggerInterface;
use Voucher\MessageQueue\Interfaces\MessageValidatorInterface;

class OrderCreatedMessageValidator implements MessageValidatorInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function isValid(?array $messageData): bool
    {
        if ($messageData === null) {
            $this->logger->err('Empty message data. Aborting.');
            return false;
        }

        if (!$this->validateMessage($messageData)) {
            return false;
        }

        return true;
    }

    private function validateMessage($messageData): bool
    {
        foreach (['id', 'amount', 'currency'] as $key) {
            if (!array_key_exists($key, $messageData)) {
                $this->logger->err(sprintf('Missing message key: %s. Aborting.', $key));
                return false;
            }
        }

        return true;
    }
}
