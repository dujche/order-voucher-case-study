<?php

namespace Voucher\Strategy;

use Money\Money;

class QueueMessageValueObject
{
    private ?int $id;

    private ?Money $money;

    public function __construct(?int $id = null, ?Money $money = null)
    {
        $this->id = $id;
        $this->money = $money;
    }

    public function isValid(): bool
    {
        return $this->id !== null && $this->money !== null;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMoney(): ?Money
    {
        return $this->money;
    }
}
