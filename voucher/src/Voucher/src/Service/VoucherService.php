<?php

namespace Voucher\Service;

use Voucher\Entity\VoucherEntity;
use Voucher\Table\VoucherTable;

class VoucherService
{
    private VoucherTable $voucherTable;

    public function __construct(VoucherTable $voucherTable)
    {
        $this->voucherTable = $voucherTable;
    }

    public function add(VoucherEntity $voucher): bool
    {
        $result = $this->voucherTable->add($voucher);
        if ($result) {
            $voucher->setId((int)$this->voucherTable->getLastInsertValue());
        }

        return $result;
    }

    public function getOneByOrderId(int $orderId): ?VoucherEntity
    {
        return $this->voucherTable->getOneByOrderId($orderId);
    }

    public function getAll(): array
    {
        $result = $this->voucherTable->getAll();
        if ($result === null) {
            return [];
        }

        $toReturn = [];
        /** @var VoucherEntity $item */
        foreach ($result as $item) {
            $toReturn[] = $item->toArray();
        }

        return $toReturn;
    }

    public function getById(int $orderId): ?VoucherEntity
    {
        return $this->voucherTable->getById($orderId);
    }
}
