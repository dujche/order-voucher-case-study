<?php

declare(strict_types=1);

namespace OrderTest\Table;

use DateTime;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Adapter\Driver\ConnectionInterface;
use Laminas\Db\Adapter\Driver\DriverInterface;
use Laminas\Db\Adapter\Platform\Mysql;
use Laminas\Log\LoggerInterface;
use Order\Entity\OrderEntity;
use Order\Entity\OrderEntityHydrator;
use Order\Table\OrderTable;
use PHPUnit\Framework\TestCase;

class OrderTableTest extends TestCase
{
    public function testAdd()
    {
        $expectedSQL = <<<TEXT
INSERT INTO `orders` (`id`, `amount`, `currency`, `inserted_at`, `published_at`) VALUES (NULL, '10000', 'EUR', '2021-01-01 00:00:00', NULL)
TEXT;

        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock->expects($this->once())->method('debug')
            ->with($expectedSQL);

        $hydrator = new OrderEntityHydrator();

        $orderEntityMock = new OrderEntity();
        $orderEntityMock->setAmount(10000);
        $orderEntityMock->setCurrency('EUR');

        $table = $this->getMockBuilder(OrderTable::class)
            ->setConstructorArgs(
                [
                    $this->getAdapterMock($this->getMockPlatformInterface()),
                    $hydrator,
                    $loggerMock
                ]
            )->onlyMethods(['insertWith'])->getMock();

        $table->expects($this->once())->method('insertWith')
            ->willReturn(1);

        $this->assertTrue($table->add($orderEntityMock, new DateTime('2021-01-01')));

    }

    protected function getAdapterMock(Mysql $mockPlatformInterface, int $lastGeneratedValue = 50)
    {
        $connectionMock = $this->createMock(ConnectionInterface::class);
        $connectionMock->method('getLastGeneratedValue')->willReturn($lastGeneratedValue);

        $driverMock = $this->createMock(DriverInterface::class);
        $driverMock->method('getConnection')->willReturn($connectionMock);

        $adapterMock = $this->createMock(AdapterInterface::class);
        $adapterMock->method('getDriver')->willReturn($driverMock);
        $adapterMock->method('getPlatform')->willReturn($mockPlatformInterface);
        return $adapterMock;
    }

    protected function getMockPlatformInterface()
    {
        $mockPlatformInterface = $this->getMockBuilder(Mysql::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['quoteValue'])
            ->getMock();

        $mockPlatformInterface->method('quoteValue')->willReturnCallback(function ($param) {
            return "'$param'";
        });

        return $mockPlatformInterface;
    }
}
