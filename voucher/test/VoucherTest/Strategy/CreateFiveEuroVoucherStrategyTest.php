<?php

declare(strict_types=1);

namespace VoucherTest\Strategy;

use JsonException;
use Laminas\Log\LoggerInterface;
use Money\Money;
use PHPUnit\Framework\TestCase;
use Voucher\Entity\VoucherEntity;
use Voucher\Service\VoucherService;
use Voucher\Strategy\CreateFiveEuroVoucherStrategy;
use Voucher\Strategy\CurrencyExchangeRateFetcher;
use Voucher\Strategy\QueueMessageValueObject;

class CreateFiveEuroVoucherStrategyTest extends TestCase
{
    private VoucherService $voucherService;

    private LoggerInterface $logger;

    private CreateFiveEuroVoucherStrategy $voucherStrategy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->voucherService = $this->createMock(VoucherService::class);

        $this->logger = $this->createMock(LoggerInterface::class);

        $this->voucherStrategy = new CreateFiveEuroVoucherStrategy($this->voucherService, $this->logger);
    }

    public function testWithExistingVoucher(): void
    {
        $existingVoucher = new VoucherEntity();
        $existingVoucher->setId(10);

        $this->voucherService->expects($this->once())->method('getOneByOrderId')
            ->with(20)->willReturn($existingVoucher);

        $this->logger->expects($this->once())->method('info')->with('Found existing voucher with id: 10');

        $result = $this->voucherStrategy->createVoucher(
            new QueueMessageValueObject(20, Money::EUR(4000))
        );

        $this->assertSame(10, $result);
    }

    public function testWithOrderAmountLessThan100Eur(): void
    {
        $existingVoucher = new VoucherEntity();
        $existingVoucher->setId(10);

        $this->voucherService->expects($this->once())->method('getOneByOrderId')
            ->with(20)->willReturn(null);

        $this->logger->expects($this->once())->method('info')
            ->with('Value of the order (40.00 EUR) is less than 100 EUR. Not creating voucher.');

        $result = $this->voucherStrategy->createVoucher(
            new QueueMessageValueObject(20, Money::EUR(4000))
        );

        $this->assertNull($result);
    }

    public function testWithOrderAmountGreaterOrEqual100Eur(): void
    {
        $existingVoucher = new VoucherEntity();
        $existingVoucher->setId(10);

        $this->voucherService->expects($this->once())->method('getOneByOrderId')
            ->with(20)->willReturn(null);

        $this->voucherService->expects($this->once())->method('add')
            ->with(
                $this->callback(function (VoucherEntity $voucher) {
                    return $voucher->getAmount() === 500 &&
                        $voucher->getCurrency() === 'EUR' &&
                        $voucher->getOrderId() === 20;
                })
            );

        $this->logger->expects($this->once())->method('debug')
            ->with('Creating new voucher...');

        $result = $this->voucherStrategy->createVoucher(
            new QueueMessageValueObject(20, Money::EUR(10000))
        );

        $this->assertNull($result);
    }
}
