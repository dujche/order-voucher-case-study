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
}
