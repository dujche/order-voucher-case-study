<?php

declare(strict_types=1);

namespace Voucher\Entity;

use DateTime;

class VoucherEntity
{
    private ?int $id = null;

    private int $orderId;

    private int $amount;

    private string $currency;

    private DateTime $insertedAt;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     */
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getOrderId(): int
    {
        return $this->orderId;
    }

    /**
     * @param int $orderId
     */
    public function setOrderId(int $orderId): void
    {
        $this->orderId = $orderId;
    }

    /**
     * @return int
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * @param int $amount
     */
    public function setAmount(int $amount): void
    {
        $this->amount = $amount;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     */
    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
    }

    /**
     * @return DateTime
     */
    public function getInsertedAt(): DateTime
    {
        return $this->insertedAt;
    }

    /**
     * @param DateTime $insertedAt
     */
    public function setInsertedAt(DateTime $insertedAt): void
    {
        $this->insertedAt = $insertedAt;
    }
}
