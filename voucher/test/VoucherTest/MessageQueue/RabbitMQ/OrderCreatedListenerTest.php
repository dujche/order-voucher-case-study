<?php

declare(strict_types=1);

namespace VoucherTest\MessageQueue\RabbitMQ;

use Exception;
use Laminas\Log\LoggerInterface;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PHPUnit\Framework\TestCase;
use Voucher\MessageQueue\Interfaces\ConsumerInterface;
use Voucher\MessageQueue\Interfaces\ListenerInterface;
use Voucher\MessageQueue\RabbitMQ\OrderCreatedListener;

class OrderCreatedListenerTest extends TestCase
{
    private LoggerInterface $logger;

    private ConsumerInterface $consumer;

    private AMQPChannel $channel;

    private AMQPStreamConnection $connection;

    private ListenerInterface $listener;

    public function setUp(): void
    {
        parent::setUp();

        $this->logger = $this->createMock(LoggerInterface::class);
        $this->consumer = $this->createMock(ConsumerInterface::class);
        $this->channel = $this->createMock(AMQPChannel::class);
        $this->connection = $this->createMock(AMQPStreamConnection::class);

        $this->listener = new OrderCreatedListener(
            $this->logger,
            $this->consumer,
            $this->channel,
            $this->connection
        );
    }

    public function testConsumeThrowsException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('foo');

        $this->consumer->expects($this->once())->method('consume')
            ->willThrowException(new Exception('foo'));

        $this->logger->expects($this->once())->method('err');

        $this->listener->listen();
    }

    public function testCloseThrowsException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('bar');

        $this->connection->expects($this->once())->method('close')
            ->willThrowException(new Exception('bar'));

        $this->logger->expects($this->exactly(2))->method('err');

        $this->listener->listen();
    }

    /**
     * @throws Exception
     */
    public function testConsumePasses(): void
    {
        $this->logger->expects($this->never())->method('err');
        $this->listener->listen();
    }
}
