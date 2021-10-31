<?php

declare(strict_types=1);

namespace Voucher\Table;

use DateTime;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\Hydrator\HydratorInterface;
use Laminas\Log\LoggerInterface;
use Voucher\Entity\VoucherEntity;

class VoucherTable extends TableGateway
{
    public const DB_TABLE_NAME = 'vouchers';

    private HydratorInterface $hydrator;

    private LoggerInterface $logger;

    public function __construct(AdapterInterface $adapter, HydratorInterface $hydrator, LoggerInterface $logger)
    {
        parent::__construct(static::DB_TABLE_NAME, $adapter);

        $this->logger = $logger;
        $this->hydrator = $hydrator;
    }

    public function add(VoucherEntity $voucher, ?DateTime $insertedAt = null): bool
    {
        $voucher->setInsertedAt($insertedAt ?: new DateTime());
        $data = $this->hydrator->extract($voucher);

        $insert = $this->getSql()->insert();
        $insert->values($data);

        $this->logger->debug($insert->getSqlString($this->getAdapter()->getPlatform()));

        return $this->insertWith($insert) === 1;
    }

    public function getOneByOrderId(int $orderId): ?VoucherEntity
    {
        $select = $this->getSql()->select();

        $select->where->equalTo('order_id', $orderId);

        $stmt = $this->getSql()->prepareStatementForSqlObject($select);
        $this->logger->debug($select->getSqlString($this->getAdapter()->getPlatform()));

        $result = $stmt->execute();

        if ($result instanceof ResultInterface && $result->isQueryResult() && $result->count() > 0) {
            return $this->hydrator->hydrate($result->current(), new VoucherEntity());
        }

        return null;
    }
}
