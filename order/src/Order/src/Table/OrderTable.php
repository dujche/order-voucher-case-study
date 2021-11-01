<?php

declare(strict_types=1);

namespace Order\Table;

use DateTime;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\ResultSet\HydratingResultSet;
use Laminas\Db\Sql\Select;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\Hydrator\HydratorInterface;
use Laminas\Log\LoggerInterface;
use Order\Entity\OrderEntity;

class OrderTable extends TableGateway
{
    public const DB_TABLE_NAME = 'orders';

    private HydratorInterface $hydrator;

    private LoggerInterface $logger;

    public function __construct(AdapterInterface $adapter, HydratorInterface $hydrator, LoggerInterface $logger)
    {
        parent::__construct(static::DB_TABLE_NAME, $adapter);

        $this->logger = $logger;
        $this->hydrator = $hydrator;
    }

    public function add(OrderEntity $order, ?DateTime $insertedAt = null): bool
    {
        $order->setInsertedAt($insertedAt ?: new DateTime());
        $data = $this->hydrator->extract($order);

        $insert = $this->getSql()->insert();
        $insert->values($data);

        $this->logger->debug($insert->getSqlString($this->getAdapter()->getPlatform()));

        return $this->insertWith($insert) === 1;
    }

    public function setPublished(int $orderId, ?DateTime $publishedAt = null): bool
    {
        $update = $this->getSql()->update();
        $update->set(['published_at' => ($publishedAt ?: new DateTime())->format('Y-m-d H:i:s')])
            ->where->equalTo('id', $orderId);

        $this->logger->debug($update->getSqlString($this->getAdapter()->getPlatform()));

        return $this->updateWith($update) === 1;
    }

    public function getAll(): ?HydratingResultSet
    {
        $select = $this->getSql()->select();
        return $this->selectMany($select);
    }

    public function getAllUnpublished(): ?HydratingResultSet
    {
        $select = $this->getSql()->select();
        $select->where->isNull('published_at');
        return $this->selectMany($select);
    }

    public function getById(int $orderId): ?OrderEntity
    {
        $select = $this->getSql()->select();
        $select->where->equalTo('id', $orderId);

        $stmt = $this->getSql()->prepareStatementForSqlObject($select);
        if ($this->logger) {
            $this->logger->debug($select->getSqlString($this->getAdapter()->getPlatform()));
        }

        $result = $stmt->execute();

        if ($result instanceof ResultInterface && $result->isQueryResult() && $result->count() > 0) {
            return $this->hydrator->hydrate($result->current(), new OrderEntity());
        }

        return null;
    }

    /**
     * @param Select $select
     * @return HydratingResultSet|null
     */
    private function selectMany(Select $select): ?HydratingResultSet
    {
        $stmt = $this->getSql()->prepareStatementForSqlObject($select);
        if ($this->logger) {
            $this->logger->debug($select->getSqlString($this->getAdapter()->getPlatform()));
        }
        $result = $stmt->execute();

        if ($result instanceof ResultInterface && $result->isQueryResult() && $result->count() > 0) {
            $resultSet = new HydratingResultSet($this->hydrator, new OrderEntity());

            return $resultSet->initialize($result);
        }

        return null;
    }
}
