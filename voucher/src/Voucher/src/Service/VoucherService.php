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
}
