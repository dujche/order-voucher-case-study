<?php

namespace Voucher\Strategy;

use Laminas\Log\LoggerInterface;
use Money\Currencies\ISOCurrencies;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Money;
use Voucher\Entity\VoucherEntity;
use Voucher\Interfaces\CreateVoucherStrategyInterface;
use Voucher\Service\VoucherService;

class CreateFiveEuroVoucherStrategy implements CreateVoucherStrategyInterface
{
    private VoucherService $voucherService;

    private LoggerInterface $logger;

    public function __construct(VoucherService $voucherService, LoggerInterface $logger)
    {
        $this->voucherService = $voucherService;
        $this->logger = $logger;
    }

    public function createVoucher(QueueMessageValueObject $messageValueObject): ?int
    {
        $existingVoucher = $this->voucherService->getOneByOrderId($messageValueObject->getId());

        if ($existingVoucher !== null) {
            $this->logger->info("Found existing voucher with id: ". $existingVoucher->getId());
            return $existingVoucher->getId();
        }

        if ($messageValueObject->getMoney()->lessThan(Money::EUR(10000))) {
            $currencies = new ISOCurrencies();
            $moneyFormatter = new DecimalMoneyFormatter($currencies);
            $this->logger->info(
                "Value of the order (" . $moneyFormatter->format($messageValueObject->getMoney()) . " EUR) is less than 100 EUR. Not creating voucher."
            );
            return null;
        }

        $voucherEntity = new VoucherEntity();
        $voucherEntity->setOrderId($messageValueObject->getId());
        $voucherEntity->setAmount(500);
        $voucherEntity->setCurrency('EUR');

        $this->logger->debug("Creating new voucher...");
        $this->voucherService->add($voucherEntity);

        return $voucherEntity->getId();
    }
}
