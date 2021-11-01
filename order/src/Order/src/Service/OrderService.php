<?php

declare(strict_types=1);

namespace Order\Service;

use Order\Entity\OrderEntity;
use Order\Table\OrderTable;

class OrderService
{
    private OrderTable $orderTable;

    public function __construct(OrderTable $orderTable)
    {
        $this->orderTable = $orderTable;
    }

    public function add(OrderEntity $order): bool
    {
        $result = $this->orderTable->add($order);
        if ($result) {
            $order->setId((int)$this->orderTable->getLastInsertValue());
        }

        return $result;
    }

    public function setPublished(int $orderId): bool
    {
        return $this->orderTable->setPublished($orderId);
    }

    public function getAll(): array
    {
        $result = $this->orderTable->getAll();
        if ($result === null) {
            return [];
        }

        $toReturn = [];
        /** @var OrderEntity $item */
        foreach($result as $item) {
            $toReturn[] = $item;
        }

        return $toReturn;
    }

    public function getAllUnpublished(): array
    {
        $result = $this->orderTable->getAllUnpublished();
        if ($result === null) {
            return [];
        }

        $toReturn = [];
        /** @var OrderEntity $item */
        foreach($result as $item) {
            $toReturn[] = $item;
        }

        return $toReturn;
    }

    public function getById(int $orderId): ?OrderEntity
    {
        return $this->orderTable->getById($orderId);
    }
}
