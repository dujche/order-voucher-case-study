<?php

declare(strict_types=1);

namespace Voucher\MessageQueue\Kafka\Factory;

use Interop\Container\ContainerInterface;
use Laminas\Log\LoggerInterface;
use Voucher\Interfaces\ListenerInterface;
use Voucher\MessageQueue\Kafka\OrderCreatedConsumer;
use Voucher\MessageQueue\Kafka\OrderCreatedListener;
use Voucher\MessageQueue\Kafka\OrderCreatedMessageHandler;

class OrderCreatedListenerFactory
{
    private const ORDER_CREATED_CHANNEL_NAME = 'order_created';

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ListenerInterface
    {
        $logger = $container->get(LoggerInterface::class);

        $conf = new \RdKafka\Conf();

        $kafkaConfiguration = $container->get('config')['kafka'] ?? null;

        $conf->set('group.id', $kafkaConfiguration['options']['groupId'] ?? '');

        $brokerList = '';
        foreach ($kafkaConfiguration['brokers'] as $brokerConfig) {
            $brokerList .= sprintf("%s:%s,", ($brokerConfig['host'] ?? ''), ($brokerConfig['port'] ?? ''));
        }

        $conf->set('metadata.broker.list', rtrim($brokerList, ','));

        $conf->set('auto.offset.reset', 'earliest');

        $kafkaConsumer = new \RdKafka\KafkaConsumer($conf);

        $kafkaConsumer->subscribe([static::ORDER_CREATED_CHANNEL_NAME]);

        $consumer = new OrderCreatedConsumer($kafkaConsumer, $container->get(OrderCreatedMessageHandler::class), $logger);

        return new OrderCreatedListener($logger, $consumer);
    }
}
