<?php

declare(strict_types=1);

namespace VoucherTest\MessageQueue\RabbitMQ\Factory;

use Interop\Container\ContainerInterface;
use Laminas\Log\LoggerInterface;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Voucher\MessageQueue\Interfaces\MessageHandlerInterface;
use Voucher\MessageQueue\RabbitMQ\Factory\OrderCreatedListenerFactory;
use Voucher\MessageQueue\RabbitMQ\OrderCreatedMessageHandler;
use Voucher\MessageQueue\RabbitMQ\RabbitMQConnection;

class OrderCreatedListenerFactoryTest extends TestCase
{
    /**
     * @var ContainerInterface|MockObject $container
     */
    private $container;

    private OrderCreatedListenerFactory $factory;

    public function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
        $this->factory = new OrderCreatedListenerFactory();
    }

    public function testInvoke(): void
    {
        $streamConnectionMock = $this->createMock(AMQPStreamConnection::class);
        $streamConnectionMock->expects($this->once())
            ->method('channel')
            ->willReturn($this->createMock(AMQPChannel::class));

        /** @var MockObject|RabbitMQConnection$rabbitMQConnection */
        $rabbitMQConnection = $this->getMockBuilder(RabbitMQConnection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $rabbitMQConnection->expects($this->once())
            ->method('getConnection')
            ->willReturn($streamConnectionMock);

        $this->container->expects($this->exactly(3))
            ->method('get')
            ->withConsecutive(
                [LoggerInterface::class],
                [RabbitMQConnection::class],
                [OrderCreatedMessageHandler::class],
            )
            ->willReturn(
                $this->createMock(LoggerInterface::class),
                $rabbitMQConnection,
                $this->createMock(MessageHandlerInterface::class)
            );

        $this->factory->__invoke($this->container, '', []);
    }
}
