<?php

declare(strict_types=1);

namespace OrderTest\Service;

use DateTime;
use Laminas\Db\ResultSet\HydratingResultSet;
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

    public function testSetPublished(): void
    {
        $tableMock = $this->createMock(OrderTable::class);
        $tableMock->expects($this->once())->method('setPublished')->with(100)->willReturn(true);

        $service = new OrderService($tableMock);
        $this->assertTrue($service->setPublished(100));
    }

    public function testGetById(): void
    {
        $tableMock = $this->createMock(OrderTable::class);
        $tableMock->expects($this->once())->method('getById')->with(10)->willReturn(null);

        $service = new OrderService($tableMock);
        $this->assertNull($service->getById(10));
    }

    public function testGetAllNoResults(): void
    {
        $tableMock = $this->createMock(OrderTable::class);
        $tableMock->expects($this->once())->method('getAll')->with()->willReturn(null);

        $service = new OrderService($tableMock);
        $this->assertSame([], $service->getAll());
    }

    public function testGetAllWithResults(): void
    {
        $orderEntity = new OrderEntity();
        $orderEntity->setId(10);
        $orderEntity->setAmount(1000);
        $orderEntity->setCurrency('EUR');
        $orderEntity->setInsertedAt(new DateTime('2021-01-01'));

        $resultSetMock = $this->createMock(HydratingResultSet::class);
        $resultSetMock->expects($this->exactly(2))->method('valid')
            ->willReturnOnConsecutiveCalls(true, false);

        $resultSetMock->expects($this->once())->method('current')
            ->willReturn($orderEntity);

        $tableMock = $this->createMock(OrderTable::class);
        $tableMock->expects($this->once())->method('getAll')->with()->willReturn($resultSetMock);

        $service = new OrderService($tableMock);
        $this->assertSame(
            [
                [
                    'id' => 10,
                    'amount' => 1000,
                    'currency' => 'EUR',
                    'insertedAt' => '2021-01-01 00:00:00',
                    'publishedAt' => null
                ]
            ],
            $service->getAll()
        );
    }
}
