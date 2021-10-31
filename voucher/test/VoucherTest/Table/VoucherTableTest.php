<?php

declare(strict_types=1);

namespace VoucherTest\Table;

use DateTime;
use Laminas\Db\Sql\Sql;
use Laminas\Log\LoggerInterface;
use PHPUnit\Framework\TestCase;
use Voucher\Entity\VoucherEntity;
use Voucher\Entity\VoucherEntityHydrator;
use Voucher\Table\VoucherTable;

class VoucherTableTest extends TestCase
{
    use TableTestHelper;

    public function testAdd(): void
    {
        $expectedSQL = <<<TEXT
INSERT INTO `vouchers` (`id`, `order_id`, `amount`, `currency`, `inserted_at`) VALUES (NULL, '10', '500', 'EUR', '2021-01-01 00:00:00')
TEXT;

        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock->expects($this->once())->method('debug')
            ->with($expectedSQL);

        $hydrator = new VoucherEntityHydrator();

        $voucherEntity = new VoucherEntity();
        $voucherEntity->setOrderId(10);
        $voucherEntity->setAmount(500);
        $voucherEntity->setCurrency('EUR');

        $table = $this->getMockBuilder(VoucherTable::class)
            ->setConstructorArgs(
                [
                    $this->getAdapterMock($this->getMockPlatformInterface()),
                    $hydrator,
                    $loggerMock
                ]
            )->onlyMethods(['insertWith'])->getMock();

        $table->expects($this->once())->method('insertWith')
            ->willReturn(1);

        $this->assertTrue($table->add($voucherEntity, new DateTime('2021-01-01')));
    }

    public function testGetOneByOrderIdReturnsNoResults(): void
    {
        $expectedSQL = <<<TEXT
SELECT `vouchers`.* FROM `vouchers` WHERE `order_id` = '10'
TEXT;
        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock->expects($this->once())->method('debug')
            ->with($expectedSQL);

        $adapterMock = $this->getAdapterMock($this->getMockPlatformInterface());

        $table = $this->getMockBuilder(VoucherTable::class)
            ->setConstructorArgs(
                [
                    $adapterMock,
                    new VoucherEntityHydrator(),
                    $loggerMock
                ]
            )->onlyMethods(['getSql'])->getMock();

        $sqlMock = $this->getMockBuilder(Sql::class)
            ->setConstructorArgs([$adapterMock, $table->getTable()])
            ->onlyMethods(['prepareStatementForSqlObject'])->getMock();

        $table->method('getSql')
            ->willReturn($sqlMock);

        $this->getSqlQueryExpectations(
            $sqlMock,
            $this->getMockPlatformInterface(),
            $expectedSQL,
            $this->setupStatementMockWithNoResults()
        );

        $this->assertNull($table->getOneByOrderId(10));
    }

    public function testGetOneByOrderIdReturnsVoucher(): void
    {
        $expectedSQL = <<<TEXT
SELECT `vouchers`.* FROM `vouchers` WHERE `order_id` = '10'
TEXT;
        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock->expects($this->once())->method('debug')
            ->with($expectedSQL);

        $adapterMock = $this->getAdapterMock($this->getMockPlatformInterface());

        $table = $this->getMockBuilder(VoucherTable::class)
            ->setConstructorArgs(
                [
                    $adapterMock,
                    new VoucherEntityHydrator(),
                    $loggerMock
                ]
            )->onlyMethods(['getSql'])->getMock();

        $sqlMock = $this->getMockBuilder(Sql::class)
            ->setConstructorArgs([$adapterMock, $table->getTable()])
            ->onlyMethods(['prepareStatementForSqlObject'])->getMock();

        $table->method('getSql')
            ->willReturn($sqlMock);

        $this->getSqlQueryExpectations(
            $sqlMock,
            $this->getMockPlatformInterface(),
            $expectedSQL,
            $this->setupStatementMockWithSingleResult(['id' => 10, 'order_id' => 50])
        );

        $result = $table->getOneByOrderId(10);
        $this->assertNotNull($result);
        $this->assertSame(10, $result->getId());
        $this->assertSame(50, $result->getOrderId());
    }
}
