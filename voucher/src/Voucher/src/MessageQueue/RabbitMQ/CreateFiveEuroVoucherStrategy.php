<?php

namespace Voucher\MessageQueue\RabbitMQ;

use Voucher\MessageQueue\Interfaces\CreateVoucherStrategyInterface;

class CreateFiveEuroVoucherStrategy implements CreateVoucherStrategyInterface
{
    public function createVoucher(?array $messageData): ?int
    {
        // TODO: Implement createVoucher() method.
        return null;
    }
}
