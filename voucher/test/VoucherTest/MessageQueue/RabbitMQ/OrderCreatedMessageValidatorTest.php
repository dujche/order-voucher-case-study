<?php

declare(strict_types=1);

namespace VoucherTest\MessageQueue\RabbitMQ;

use Laminas\Log\LoggerInterface;
use PHPUnit\Framework\TestCase;
use Voucher\MessageQueue\Interfaces\MessageValidatorInterface;
use Voucher\MessageQueue\RabbitMQ\OrderCreatedMessageValidator;

class OrderCreatedMessageValidatorTest extends TestCase
{
    private LoggerInterface $logger;

    private MessageValidatorInterface $messageValidator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logger = $this->createMock(LoggerInterface::class);
        $this->messageValidator = new OrderCreatedMessageValidator($this->logger);
    }

    public function testIsValidWithEmptyMessageData(): void
    {
        $this->logger->expects($this->once())->method('err')
            ->with('Empty message data. Aborting.');

        $this->assertFalse($this->messageValidator->isValid(null));
    }

    public function testIsValidWithMissingKey(): void
    {
        $this->logger->expects($this->once())->method('err')
            ->with('Missing message key: amount. Aborting.');

        $this->assertFalse($this->messageValidator->isValid([ 'id' => 150 ]));
    }

    public function testIsValidPasses(): void
    {
        $this->logger->expects($this->never())->method('err');
        $this->assertTrue($this->messageValidator->isValid([ 'id' => 150, 'amount' => 9999, 'currency' => 'USD' ]));
    }
}
