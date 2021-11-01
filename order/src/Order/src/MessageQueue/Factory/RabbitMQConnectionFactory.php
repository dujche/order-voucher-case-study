<?php

declare(strict_types=1);

namespace Order\MessageQueue\Factory;

use Exception;
use Interop\Container\ContainerInterface;
use Laminas\Log\LoggerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Order\Exception\InvalidArgumentException;
use Order\Exception\RuntimeException;
use Order\MessageQueue\RabbitMQConnection;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class RabbitMQConnectionFactory implements FactoryInterface
{
    /**
     * @throws RuntimeException|InvalidArgumentException
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ?RabbitMQConnection
    {
        $logger = $container->get(LoggerInterface::class);

        try {
            $connection = $this->createConnection($this->getRabbitMqConfiguration($container));
            if (!$connection) {
                throw new RuntimeException(
                    sprintf('%s failed to build a connection.', AMQPStreamConnection::class)
                );
            }

            return new RabbitMQConnection($connection);
        } catch (Exception $exception) {

            $logMessage = sprintf(
                '%s failed to build a connection. Reason: %s',
                AMQPStreamConnection::class,
                $exception->getMessage()
            );

            $logger->err($logMessage);

            return null;
        }
    }

    /**
     * @param ContainerInterface $container
     * @return array
     * @throws InvalidArgumentException
     */
    private function getRabbitMqConfiguration(ContainerInterface $container): array
    {
        $messageQueueConfiguration = $container->get('config')['mq'] ?? null;
        if (!empty($messageQueueConfiguration['nodeConfigs'] ?? null)) {
            return $messageQueueConfiguration;
        }

        throw new InvalidArgumentException('Invalid RabbitMQ configuration.');
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
