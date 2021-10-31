<?php

declare(strict_types=1);

namespace Voucher\MessageQueue\RabbitMQ;

use Voucher\Interfaces\MessageValidatorInterface;
use Voucher\Strategy\QueueMessageValueObject;

class OrderCreatedMessageValidator implements MessageValidatorInterface
{
    public function isValid(QueueMessageValueObject $messageValueObject): bool
    {
        return $messageValueObject->isValid();
    }
}
