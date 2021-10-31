<?php

declare(strict_types=1);

namespace Voucher\Interfaces;

use Voucher\Strategy\QueueMessageValueObject;

interface MessageParserInterface
{
    public function parseMessage(?string $message): QueueMessageValueObject;
}
