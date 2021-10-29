<?php

declare(strict_types=1);

namespace OrderTest\Service;

use Order\Entity\OrderEntity;
use Order\Service\OrderService;
use Order\Table\OrderTable;
use PHPUnit\Framework\TestCase;

class OrderServiceTest extends TestCase
{
    public function testAddFailsOnTable(): void
    {
        $orderEntity = new OrderEntity();

        $tableMock = $this->createMock(OrderTable::class);
        $tableMock->expects($this->once())->method('add')->with($orderEntity)->willReturn(false);
        $tableMock->expects($this->never())->method('getLastInsertValue');

        $service = new OrderService($tableMock);
        $this->assertFalse($service->add($orderEntity));
    }

    public function testAddSucceedsOnTable(): void
    {
        $orderEntity = new OrderEntity();

        $tableMock = $this->createMock(OrderTable::class);
        $tableMock->expects($this->once())->method('add')->with($orderEntity)->willReturn(true);
        $tableMock->expects($this->once())->method('getLastInsertValue')->willReturn("50");

        $service = new OrderService($tableMock);
        $this->assertTrue($service->add($orderEntity));
        $this->assertEquals(50, $orderEntity->getId());
    }
}
