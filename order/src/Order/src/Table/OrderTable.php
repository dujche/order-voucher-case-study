<?php

declare(strict_types=1);

namespace Order\Table;

use DateTime;
use Laminas\Db\Adapter\AdapterInterface;
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
}
