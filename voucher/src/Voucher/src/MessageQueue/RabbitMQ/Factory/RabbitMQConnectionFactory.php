<?php

namespace Voucher\MessageQueue\RabbitMQ\Factory;

use Exception;
use Interop\Container\ContainerInterface;
use InvalidArgumentException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use RuntimeException;
use Voucher\MessageQueue\RabbitMQ\RabbitMQConnection;

class RabbitMQConnectionFactory implements FactoryInterface
{
    /**
     * @throws Exception
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): RabbitMQConnection
    {
        $connection = $this->createConnection($this->getRabbitMqConfiguration($container));

        if (!$connection) {
            throw new RuntimeException(
                sprintf('%s failed to build a connection.', AMQPStreamConnection::class)
            );
        }

        return new RabbitMQConnection($connection);
    }

    /**
     * @param ContainerInterface $container
     * @return array
     * @throws InvalidArgumentException
     */
    private function getRabbitMqConfiguration(ContainerInterface $container): array
    {
        $messageQueueConfiguration = $container->get('config')['mq'] ?? null;
        if (!$messageQueueConfiguration || !is_array($messageQueueConfiguration)) {
            throw new InvalidArgumentException('Invalid RabbitMQ configuration.');
        }

        return $messageQueueConfiguration;
    }

    /**
     * @param array $messageQueueConfiguration
     * @return mixed
     * @throws Exception
     * @codeCoverageIgnore
     */
    protected function createConnection(array $messageQueueConfiguration)
    {
        $nodeConfigs = array_values($messageQueueConfiguration['nodeConfigs']);
        foreach (array_keys($nodeConfigs) as $nodeConfigKey) {
            if (isset($nodeConfigs[$nodeConfigKey]['username']) && !isset($nodeConfigs[$nodeConfigKey]['user'])) {
                $nodeConfigs[$nodeConfigKey]['user'] = $nodeConfigs[$nodeConfigKey]['username'];
                unset($nodeConfigs[$nodeConfigKey]['username']);
            }
        }

        return AMQPStreamConnection::create_connection(
            $nodeConfigs,
            $messageQueueConfiguration['options'] ?? []
        );
    }
}
