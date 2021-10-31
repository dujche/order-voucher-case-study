<?php

declare(strict_types=1);

namespace Order\Entity;

use DateTime;

class OrderEntity
{
    private ?int $id = null;

    private int $amount;

    private string $currency;

    private DateTime $insertedAt;

    private ?DateTime $publishedAt = null;

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

    /**
     * @return DateTime|null
     */
    public function getPublishedAt(): ?DateTime
    {
        return $this->publishedAt;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'insertedAt' => $this->insertedAt ? $this->insertedAt->format('Y-m-d H:i:s') : null,
            'publishedAt' => $this->publishedAt ? $this->publishedAt->format('Y-m-d H:i:s') : null
        ];
    }
}
