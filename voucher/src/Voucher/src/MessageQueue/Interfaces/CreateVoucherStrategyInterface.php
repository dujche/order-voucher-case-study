<?php

declare(strict_types=1);

namespace Voucher\MessageQueue\Interfaces;

interface CreateVoucherStrategyInterface
{
    public function createVoucher(?array $messageData): ?int;
}
