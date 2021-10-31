<?php

declare(strict_types=1);

namespace VoucherTest\Table;

use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Adapter\Driver\ConnectionInterface;
use Laminas\Db\Adapter\Driver\DriverInterface;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\Adapter\Driver\StatementInterface;
use Laminas\Db\Adapter\Platform\Mysql;
use Laminas\Db\Sql\AbstractPreparableSql;
use PHPUnit\Framework\MockObject\MockObject;

trait TableTestHelper
{
    protected function getAdapterMock(Mysql $mockPlatformInterface, int $lastGeneratedValue = 50): AdapterInterface
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

    protected function getMockPlatformInterface(): Mysql
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

    /**
     * @param $sqlMock
     * @param $mockPlatformInterface
     * @param string $expectedSql
     * @param $statementMock
     */
    protected function getSqlQueryExpectations(
        $sqlMock,
        $mockPlatformInterface,
        string $expectedSql,
        $statementMock
    ): void {
        $sqlMock->expects($this->once())->method('prepareStatementForSqlObject')
            ->with(
                $this->callback(
                    function (AbstractPreparableSql $sql) use ($mockPlatformInterface, $expectedSql) {
                        if ($expectedSql === $sql->getSqlString($mockPlatformInterface)) {
                            return true;
                        }
                        $this->fail($sql->getSqlString($mockPlatformInterface));
                    }
                )
            )
            ->willReturn($statementMock);
    }

    /**
     * @return MockObject|StatementInterface
     */
    protected function setupStatementMockWithNoResults()
    {
        $statementMock = $this->createMock(StatementInterface::class);
        $statementMock->expects($this->once())->method('execute');

        return $statementMock;
    }

    /**
     * @return MockObject|StatementInterface
     */
    protected function setupStatementMockWithSingleResult($toReturn)
    {
        $resultSetMock = $this->createMock(ResultInterface::class);
        $resultSetMock->expects($this->once())->method('isQueryResult')->willReturn(true);
        $resultSetMock->method('count')->willReturn(1);
        $resultSetMock->expects($this->once())->method('current')->willReturn($toReturn);

        $statementMock = $this->createMock(StatementInterface::class);
        $statementMock->expects($this->once())->method('execute')->willReturn($resultSetMock);

        return $statementMock;
    }
}
