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

    public function setId($id): void
    {
        $this->id = $id ? (int)$id : null;
    }

    /**
     * @return int
     */
    public function getOrderId(): int
    {
        return $this->orderId;
    }

    public function setOrderId($orderId): void
    {
        $this->orderId = (int) $orderId;
    }

    /**
     * @return int
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    public function setAmount($amount): void
    {
        $this->amount = (int) $amount;
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

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'orderId' => $this->orderId,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'insertedAt' => $this->insertedAt ? $this->insertedAt->format('Y-m-d H:i:s') : null,
        ];
    }
}
