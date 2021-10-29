<?php

declare(strict_types=1);

namespace OrderTest\MessageQueue\Factory;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Laminas\Log\LoggerInterface;
use Order\MessageQueue\Factory\OrderCreatedMessageProducerFactory;
use Order\MessageQueue\OrderCreatedMessageProducer;
use Order\MessageQueue\RabbitMQConnection;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OrderCreatedMessageProducerFactoryTest extends TestCase
{
    /**
     * @var ContainerInterface|MockObject $container
     */
    private $container;

    private OrderCreatedMessageProducerFactory $factory;

    public function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
        $this->factory = new OrderCreatedMessageProducerFactory();
    }

    /**
     * @throws ContainerException
     */
    public function testInvoke(): void
    {
        /** @var MockObject|RabbitMQConnection$rabbitMQConnection */
        $rabbitMQConnection = $this->getMockBuilder(RabbitMQConnection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $rabbitMQConnection->expects($this->once())
            ->method('getConnection')
            ->willReturn($this->createMock(AMQPStreamConnection::class));

        $rabbitMQConnection->expects($this->once())
            ->method('buildChannel')
            ->with(OrderCreatedMessageProducer::ORDER_CREATED_CHANNEL_NAME)
            ->willReturn($this->createMock(AMQPChannel::class));

        $this->container->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(
                [RabbitMQConnection::class],
                [LoggerInterface::class]
            )
            ->willReturn(
                $rabbitMQConnection,
                $this->createMock(LoggerInterface::class)
            );

        $this->factory->__invoke($this->container, '', []);

    }
}
