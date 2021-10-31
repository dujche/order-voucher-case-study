<?php

declare(strict_types=1);

namespace VoucherTest\MessageQueue\RabbitMQ;

use PHPUnit\Framework\TestCase;
use Voucher\MessageQueue\RabbitMQ\OrderCreatedMessageValidator;
use Voucher\Strategy\QueueMessageValueObject;

class OrderCreatedMessageValidatorTest extends TestCase
{
    private QueueMessageValueObject $queueMessageValueObject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->queueMessageValueObject = $this->createMock(QueueMessageValueObject::class);
    }

    public function testIsValidWithInvalidQueueMessageValueObject(): void
    {
        $this->queueMessageValueObject->expects($this->once())->method('isValid')->willReturn(false);
        $instance = new OrderCreatedMessageValidator();
        $this->assertFalse($instance->isValid($this->queueMessageValueObject));
    }

    public function testIsValidPasses(): void
    {
        $this->queueMessageValueObject->expects($this->once())->method('isValid')->willReturn(true);
        $instance = new OrderCreatedMessageValidator();
        $this->assertTrue($instance->isValid($this->queueMessageValueObject));
    }
}
