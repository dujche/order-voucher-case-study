<?php

declare(strict_types=1);

namespace Voucher\MessageQueue\Interfaces;

interface ConsumerInterface
{
    public function consume(): void;
}
