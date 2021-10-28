<?php

declare(strict_types=1);

namespace Voucher\MessageQueue\Interfaces;

interface MessageValidatorInterface
{
    public function isValid(?array $messageData): bool;
}
