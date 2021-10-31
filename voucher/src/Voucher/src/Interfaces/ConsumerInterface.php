<?php

declare(strict_types=1);

namespace Voucher\Interfaces;

interface ConsumerInterface
{
    public function consume(): void;
}
