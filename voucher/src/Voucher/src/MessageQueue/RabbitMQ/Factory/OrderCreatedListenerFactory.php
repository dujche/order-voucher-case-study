<?php

declare(strict_types=1);

namespace Voucher\MessageQueue\RabbitMQ\Factory;

use Interop\Container\ContainerInterface;
use Laminas\Log\LoggerInterface;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Voucher\MessageQueue\RabbitMQ\OrderCreatedConsumer;
use Voucher\MessageQueue\RabbitMQ\OrderCreatedListener;
use Voucher\MessageQueue\RabbitMQ\OrderCreatedMessageHandler;
use Voucher\MessageQueue\RabbitMQ\RabbitMQConnection;

class OrderCreatedListenerFactory
{
    private const ORDER_CREATED_CHANNEL_NAME = 'order_created';

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): OrderCreatedListener
    {
        $logger = $container->get(LoggerInterface::class);

        /** @var RabbitMQConnection $rabbitMQConnection */
        $rabbitMQConnection = $container->get(RabbitMQConnection::class);
        $connection = $rabbitMQConnection->getConnection();
        $channel = $this->buildChannel($connection, static::ORDER_CREATED_CHANNEL_NAME);
        $consumer = new OrderCreatedConsumer($channel, $container->get(OrderCreatedMessageHandler::class));

        return new OrderCreatedListener($logger, $consumer, $channel, $connection);
    }

    /**
     * @param AMQPStreamConnection $connection
     * @param string $channelName
     * @return AMQPChannel
     */
    private function buildChannel(AMQPStreamConnection $connection, string $channelName): AMQPChannel
    {
        $channel = $connection->channel();
        $channel->queue_declare($channelName, false, true, false, false);
        $channel->basic_qos(null, 1, null);

        return $channel;
    }
}
