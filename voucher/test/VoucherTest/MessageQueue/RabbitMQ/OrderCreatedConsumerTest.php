<?php

declare(strict_types=1);

namespace VoucherTest\MessageQueue\RabbitMQ;

use PhpAmqpLib\Channel\AMQPChannel;
use PHPUnit\Framework\TestCase;
use Voucher\MessageQueue\Interfaces\MessageHandlerInterface;
use Voucher\MessageQueue\RabbitMQ\OrderCreatedConsumer;

class OrderCreatedConsumerTest extends TestCase
{
    public function testConsume(): void
    {
        $channelMock = $this->createMock(AMQPChannel::class);
        $channelMock->expects($this->once())->method('basic_consume');
        $messageHandlerMock = $this->createMock(MessageHandlerInterface::class);

        $instance = new OrderCreatedConsumer($channelMock, $messageHandlerMock);
        $instance->consume();
    }
}
