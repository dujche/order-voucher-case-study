<?php

declare(strict_types=1);

namespace Voucher\Interfaces;

use Voucher\Strategy\QueueMessageValueObject;

interface CreateVoucherStrategyInterface
{
    public function createVoucher(QueueMessageValueObject $messageValueObject): ?int;
}
