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

    /**
     * @param ?int $id
     */
    public function setId(?int $id): void
    {
        $this->id = $id;
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

    /**
     * @return DateTime|null
     */
    public function getPublishedAt(): ?DateTime
    {
        return $this->publishedAt;
    }

    /**
     * @param DateTime|null $publishedAt
     */
    public function setPublishedAt(?DateTime $publishedAt): void
    {
        $this->publishedAt = $publishedAt;
    }
}
