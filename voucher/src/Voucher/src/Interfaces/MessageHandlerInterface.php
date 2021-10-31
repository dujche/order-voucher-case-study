<?php

declare(strict_types=1);

namespace Voucher\Interfaces;

use PhpAmqpLib\Message\AMQPMessage;

interface MessageHandlerInterface
{
    public function handle(AMQPMessage $message): void;
}
