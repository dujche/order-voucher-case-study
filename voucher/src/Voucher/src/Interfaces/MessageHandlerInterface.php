<?php

declare(strict_types=1);

namespace Voucher\Interfaces;

interface MessageHandlerInterface
{
    public function handle($message): void;
}
