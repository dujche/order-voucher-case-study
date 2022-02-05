<?php

declare(strict_types=1);

namespace Order\MessageQueue\Factory;

use Exception;
use Interop\Container\ContainerInterface;
use Laminas\Log\LoggerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Order\MessageQueue\OrderCreatedMessageProducerKafka;

class OrderCreatedMessageProducerKafkaFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): OrderCreatedMessageProducerKafka {

        $logger = $container->get(LoggerInterface::class);

        try {

            $kafkaConfiguration = $container->get('config')['kafka'] ?? null;

            $conf = new \RdKafka\Conf();
            $conf->set('log_level', (string) $kafkaConfiguration['options']['logLevel'] ?? LOG_ERR);
            //$conf->set('debug', 'all');
            $rk = new \RdKafka\Producer($conf);
            $brokerList = '';
            foreach ($kafkaConfiguration['brokers'] as $brokerConfig) {
                $brokerList .= sprintf("%s:%s,", ($brokerConfig['host'] ?? ''), ($brokerConfig['port'] ?? ''));
            }

            $rk->addBrokers(rtrim($brokerList, ','));

            return new OrderCreatedMessageProducerKafka($logger, $rk);
        } catch (Exception $e) {
            $logger->err($e->getMessage());

            throw $e;
        }
    }
}
