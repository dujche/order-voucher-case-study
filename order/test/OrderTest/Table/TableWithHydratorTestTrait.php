<?php

namespace OrderTest\Table;

use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Adapter\Driver\ConnectionInterface;
use Laminas\Db\Adapter\Driver\DriverInterface;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\Adapter\Driver\StatementInterface;
use Laminas\Db\Adapter\Platform\Mysql;
use Laminas\Db\Sql\AbstractPreparableSql;
use Laminas\Db\Sql\Sql;
use Laminas\Log\LoggerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;
use ReflectionException;

trait TableWithHydratorTestTrait
{
    /**
     * @param string $expectedSql
     * @param $statementMock
     * @return MockObject
     * @throws ReflectionException
     */
    protected function setUpTableMock(
        string $expectedSql,
        $statementMock
    ): MockObject {

        $loggerMock = $this->createMock(LoggerInterface::class);
        $mockPlatformInterface = $this->getMockPlatformInterface();

        $adapterMock = $this->getAdapterMock($mockPlatformInterface);
        $table = $this->getTableMock($adapterMock, $loggerMock);

        $sqlMock = $this->getSqlMock($adapterMock, $table);

        $this->getSqlQueryExpectations($sqlMock, $mockPlatformInterface, $expectedSql, $statementMock);

        $tableReflection = new ReflectionClass($table);
        $property = $tableReflection->getProperty('sql');
        $property->setAccessible(true);
        $property->setValue($table, $sqlMock);

        $table->method('getSql')->willReturn($sqlMock);
        return $table;
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

    /**
     * @return MockObject|StatementInterface
     */
    protected function setupStatementMockWithResults()
    {
        $resultSetMock = $this->createMock(ResultInterface::class);
        $resultSetMock->expects($this->once())->method('isQueryResult')->willReturn(true);
        $resultSetMock->method('count')->willReturn(1);

        $statementMock = $this->createMock(StatementInterface::class);
        $statementMock->expects($this->once())->method('execute')->willReturn($resultSetMock);

        return $statementMock;
    }

    /**
     * @param int $affectedRows
     * @return MockObject|StatementInterface
     */
    protected function setupStatementMockWithAffectedRows(int $affectedRows): StatementInterface
    {
        $resultInterface = $this->createMock(ResultInterface::class);
        $resultInterface->expects($this->once())->method('getAffectedRows')->willReturn($affectedRows);

        $statementInterface = $this->createMock(StatementInterface::class);
        $statementInterface->expects($this->once())->method('execute')->willReturn($resultInterface);

        return $statementInterface;
    }


    /**
     * @return Mysql|MockObject
     */
    protected function getMockPlatformInterface()
    {
        /** @var Mysql|MockObject $mockPlatformInterface */
        $mockPlatformInterface = $this->getMockBuilder(Mysql::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['quoteValue'])
            ->getMock();

        $mockPlatformInterface->method('quoteValue')->willReturnArgument(0);
        return $mockPlatformInterface;
    }

    /**
     * @return mixed
     */
    protected function getSqlMock($adapterMock, $table)
    {
        return $this->getMockBuilder(Sql::class)
            ->setConstructorArgs([$adapterMock, $table->getTable()])
            ->onlyMethods(['prepareStatementForSqlObject'])->getMock();
    }

    /**
     * @param $mockPlatformInterface
     * @return mixed
     */
    protected function getAdapterMock($mockPlatformInterface)
    {
        $connectionMock = $this->createMock(ConnectionInterface::class);
        $connectionMock->method('getLastGeneratedValue')->willReturn(random_int(1, 100));

        $driverMock = $this->createMock(DriverInterface::class);
        $driverMock->method('getConnection')->willReturn($connectionMock);

        $adapterMock = $this->createMock(AdapterInterface::class);
        $adapterMock->method('getDriver')->willReturn($driverMock);
        $adapterMock->method('getPlatform')->willReturn($mockPlatformInterface);
        return $adapterMock;
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
}
