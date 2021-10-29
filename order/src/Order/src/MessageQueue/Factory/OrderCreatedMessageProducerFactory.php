<?php

declare(strict_types=1);

namespace Order\MessageQueue\Factory;

use Interop\Container\ContainerInterface;
use Laminas\Log\LoggerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Order\MessageQueue\OrderCreatedMessageProducer;
use Order\MessageQueue\RabbitMQConnection;

class OrderCreatedMessageProducerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): OrderCreatedMessageProducer
    {
        /** @var RabbitMQConnection $rabbitMQConnection */
        $rabbitMQConnection = $container->get(RabbitMQConnection::class);
        $connection = $rabbitMQConnection->getConnection();
        $channel = $rabbitMQConnection->buildChannel(OrderCreatedMessageProducer::ORDER_CREATED_CHANNEL_NAME);

        return new OrderCreatedMessageProducer(
            $container->get(LoggerInterface::class),
            $connection,
            $channel
        );
    }
}
