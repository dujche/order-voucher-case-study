<?php

declare(strict_types=1);

namespace Order\MessageQueue\Factory;

use Interop\Container\ContainerInterface;
use Laminas\Log\LoggerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Order\MessageQueue\OrderCreatedMessageProducerRabbitMQ;
use Order\MessageQueue\RabbitMQConnection;

class OrderCreatedMessageProducerRabbitMQFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): OrderCreatedMessageProducerRabbitMQ {
        /** @var RabbitMQConnection|null $rabbitMQConnection */
        $rabbitMQConnection = $container->get(RabbitMQConnection::class);
        $connection = $rabbitMQConnection ? $rabbitMQConnection->getConnection() : null;
        $channel = $rabbitMQConnection ?
            $rabbitMQConnection->buildChannel(OrderCreatedMessageProducerRabbitMQ::ORDER_CREATED_CHANNEL_NAME)
            : null;

        return new OrderCreatedMessageProducerRabbitMQ(
            $container->get(LoggerInterface::class),
            $connection,
            $channel
        );
    }
}
