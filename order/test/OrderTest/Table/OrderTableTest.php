<?php

declare(strict_types=1);

namespace OrderTest\Table;

use DateTime;
use Order\Entity\OrderEntity;
use Order\Entity\OrderEntityHydrator;
use Order\Table\OrderTable;
use PHPUnit\Framework\TestCase;

class OrderTableTest extends TestCase
{
    use TableWithHydratorTestTrait;

    public function getTableMock($adapterMock, $loggerMock): OrderTable
    {
        return $this->getMockBuilder(OrderTable::class)
            ->setConstructorArgs(
                [
                    $adapterMock,
                    new OrderEntityHydrator(),
                    $loggerMock
                ]
            )
            ->onlyMethods(['getSql'])->getMock();
    }

    public function testAdd(): void
    {
        $expectedSql = <<<TEXT
INSERT INTO `orders` (`id`, `amount`, `currency`, `inserted_at`, `published_at`) VALUES (NULL, 10000, EUR, 2021-01-01 00:00:00, NULL)
TEXT;
        $orderEntityMock = new OrderEntity();
        $orderEntityMock->setAmount(10000);
        $orderEntityMock->setCurrency('EUR');

        /** @var OrderTable $table */
        $table = $this->setUpTableMock(
            $expectedSql,
            $this->setupStatementMockWithAffectedRows(1)
        );

        $this->assertTrue($table->add($orderEntityMock, new DateTime('2021-01-01')));
    }

    public function testSetPublished(): void
    {
        $expectedSQL = <<<TEXT
UPDATE `orders` SET `published_at` = 2021-01-01 00:00:00 WHERE `id` = 10
TEXT;

        /** @var OrderTable $table */
        $table = $this->setUpTableMock(
            $expectedSQL,
            $this->setupStatementMockWithAffectedRows(1)
        );

        $this->assertTrue($table->setPublished(10, new DateTime('2021-01-01')));
    }

    public function testGetByIdNoResults(): void
    {
        $expectedSql = 'SELECT `orders`.* FROM `orders` WHERE `id` = 10';

        /** @var OrderTable $table */
        $table = $this->setUpTableMock($expectedSql, $this->setupStatementMockWithNoResults());

        $this->assertNull($table->getById(10));
    }

    public function testGetById(): void
    {
        $expectedSql = 'SELECT `orders`.* FROM `orders` WHERE `id` = 10';

        /** @var OrderTable $table */
        $table = $this->setUpTableMock($expectedSql, $this->setupStatementMockWithSingleResult(['id' => 20]));

        $this->assertInstanceOf(OrderEntity::class, $table->getById(10));
    }

    public function testGetAllNoResults(): void
    {
        $expectedSql = 'SELECT `orders`.* FROM `orders`';

        /** @var OrderTable $table */
        $table = $this->setUpTableMock($expectedSql, $this->setupStatementMockWithNoResults());

        $this->assertNull($table->getAll());
    }

    public function testGetAll(): void
    {
        $expectedSql = 'SELECT `orders`.* FROM `orders`';

        /** @var OrderTable $table */
        $table = $this->setUpTableMock($expectedSql, $this->setupStatementMockWithResults());

        $this->assertNotNull($table->getAll());
    }
}
