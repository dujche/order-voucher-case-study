<?php

declare(strict_types=1);

namespace Voucher\Interfaces;

use Voucher\Strategy\QueueMessageValueObject;

interface MessageValidatorInterface
{
    public function isValid(QueueMessageValueObject $messageValueObject): bool;
}
