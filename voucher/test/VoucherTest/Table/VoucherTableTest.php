<?php

declare(strict_types=1);

namespace VoucherTest\Table;

use DateTime;
use PHPUnit\Framework\TestCase;
use Voucher\Entity\VoucherEntity;
use Voucher\Entity\VoucherEntityHydrator;
use Voucher\Table\VoucherTable;

class VoucherTableTest extends TestCase
{
    use TableTestHelper;

    public function getTableMock($adapterMock, $loggerMock): VoucherTable
    {
        return $this->getMockBuilder(VoucherTable::class)
            ->setConstructorArgs(
                [
                    $adapterMock,
                    new VoucherEntityHydrator(),
                    $loggerMock
                ]
            )
            ->onlyMethods(['getSql'])->getMock();
    }

    public function testAdd(): void
    {
        $expectedSql = <<<TEXT
INSERT INTO `vouchers` (`id`, `order_id`, `amount`, `currency`, `inserted_at`) VALUES (NULL, 10, 500, EUR, 2021-01-01 00:00:00)
TEXT;

        $voucherEntity = new VoucherEntity();
        $voucherEntity->setOrderId(10);
        $voucherEntity->setAmount(500);
        $voucherEntity->setCurrency('EUR');

        /** @var VoucherTable $table */
        $table = $this->setUpTableMock(
            $expectedSql,
            $this->setupStatementMockWithAffectedRows(1)
        );


        $this->assertTrue($table->add($voucherEntity, new DateTime('2021-01-01')));
    }

    public function testGetOneByOrderIdReturnsNoResults(): void
    {
        $expectedSql = <<<TEXT
SELECT `vouchers`.* FROM `vouchers` WHERE `order_id` = 10
TEXT;
        /** @var VoucherTable $table */
        $table = $this->setUpTableMock($expectedSql, $this->setupStatementMockWithNoResults());

        $this->assertNull($table->getOneByOrderId(10));
    }

    public function testGetOneByOrderIdReturnsVoucher(): void
    {
        $expectedSql = <<<TEXT
SELECT `vouchers`.* FROM `vouchers` WHERE `order_id` = 50
TEXT;
        /** @var VoucherTable $table */
        $table = $this->setUpTableMock($expectedSql, $this->setupStatementMockWithSingleResult(['id' => 10, 'order_id' => 50]));

        $result = $table->getOneByOrderId(50);
        $this->assertNotNull($result);
        $this->assertSame(10, $result->getId());
        $this->assertSame(50, $result->getOrderId());
    }

    public function testGetByIdNoResults(): void
    {
        $expectedSql = 'SELECT `vouchers`.* FROM `vouchers` WHERE `id` = 10';

        /** @var VoucherTable $table */
        $table = $this->setUpTableMock($expectedSql, $this->setupStatementMockWithNoResults());

        $this->assertNull($table->getById(10));
    }

    public function testGetById(): void
    {
        $expectedSql = 'SELECT `vouchers`.* FROM `vouchers` WHERE `id` = 10';

        /** @var VoucherTable $table */
        $table = $this->setUpTableMock($expectedSql, $this->setupStatementMockWithSingleResult(['id' => 20]));

        $this->assertInstanceOf(VoucherEntity::class, $table->getById(10));
    }

    public function testGetAllNoResults(): void
    {
        $expectedSql = 'SELECT `vouchers`.* FROM `vouchers`';

        /** @var VoucherTable $table */
        $table = $this->setUpTableMock($expectedSql, $this->setupStatementMockWithNoResults());

        $this->assertNull($table->getAll());
    }

    public function testGetAll(): void
    {
        $expectedSql = 'SELECT `vouchers`.* FROM `vouchers`';

        /** @var VoucherTable $table */
        $table = $this->setUpTableMock($expectedSql, $this->setupStatementMockWithResults());

        $this->assertNotNull($table->getAll());
    }
}
