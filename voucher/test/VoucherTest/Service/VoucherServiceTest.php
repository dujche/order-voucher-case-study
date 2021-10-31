<?php

declare(strict_types=1);

namespace VoucherTest\Service;

use DateTime;
use Laminas\Db\ResultSet\HydratingResultSet;
use PHPUnit\Framework\TestCase;
use Voucher\Entity\VoucherEntity;
use Voucher\Service\VoucherService;
use Voucher\Table\VoucherTable;

class VoucherServiceTest extends TestCase
{
    private VoucherTable $voucherTable;

    private VoucherService $voucherService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->voucherTable = $this->createMock(VoucherTable::class);
        $this->voucherService = new VoucherService($this->voucherTable);
    }

    public function testAddFailsOnTable(): void
    {
        $voucherEntity = new VoucherEntity();

        $this->voucherTable->expects($this->once())->method('add')->with($voucherEntity)->willReturn(false);
        $this->voucherTable->expects($this->never())->method('getLastInsertValue');

        $this->assertFalse($this->voucherService->add($voucherEntity));
    }

    public function testAddSucceedsOnTable(): void
    {
        $voucherEntity = new VoucherEntity();

        $this->voucherTable->expects($this->once())->method('add')->with($voucherEntity)->willReturn(true);
        $this->voucherTable->expects($this->once())->method('getLastInsertValue')->willReturn("50");

        $this->assertTrue($this->voucherService->add($voucherEntity));
        $this->assertEquals(50, $voucherEntity->getId());
    }

    public function testGetOneByOrderId(): void
    {
        $this->voucherTable->expects($this->once())->method('getOneByOrderId')->with(10)->willReturn(null);

        $this->assertNull($this->voucherService->getOneByOrderId(10));
    }

    public function testGetById(): void
    {
        $this->voucherTable->expects($this->once())->method('getById')->with(10)->willReturn(null);

        $this->assertNull($this->voucherService->getById(10));
    }

    public function testGetAllNoResults(): void
    {
        $this->voucherTable->expects($this->once())->method('getAll')->with()->willReturn(null);

        $this->assertSame([], $this->voucherService->getAll());
    }

    public function testGetAllWithResults(): void
    {
        $voucherEntity = new VoucherEntity();
        $voucherEntity->setId(10);
        $voucherEntity->setOrderId(20);
        $voucherEntity->setAmount(1000);
        $voucherEntity->setCurrency('EUR');
        $voucherEntity->setInsertedAt(new DateTime('2021-01-01'));

        $resultSetMock = $this->createMock(HydratingResultSet::class);
        $resultSetMock->expects($this->exactly(2))->method('valid')
            ->willReturnOnConsecutiveCalls(true, false);

        $resultSetMock->expects($this->once())->method('current')
            ->willReturn($voucherEntity);

        $this->voucherTable->expects($this->once())->method('getAll')->with()->willReturn($resultSetMock);

        $this->assertSame(
            [
                [
                    'id' => 10,
                    'orderId' => 20,
                    'amount' => 1000,
                    'currency' => 'EUR',
                    'insertedAt' => '2021-01-01 00:00:00',
                ]
            ],
            $this->voucherService->getAll()
        );
    }
}
