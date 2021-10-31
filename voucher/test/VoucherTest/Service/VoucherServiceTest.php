<?php

declare(strict_types=1);

namespace VoucherTest\Service;

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
}
