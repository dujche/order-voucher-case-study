<?php

declare(strict_types=1);

namespace Order\MessageQueue;

use Exception;
use Laminas\Log\LoggerInterface;
use Order\Exception\RuntimeException;
use PhpAmqpLib\Message\AMQPMessage;

class OrderCreatedMessageProducerKafka implements OrderCreatedMessageProducerInterface
{
    public const ORDER_CREATED_TOPIC_NAME = 'order_created';

    private LoggerInterface $logger;

    private \RdKafka\Producer $producer;

    public function __construct(
        LoggerInterface $logger,
        \RdKafka\Producer $producer
    ) {
        $this->logger = $logger;
        $this->producer = $producer;
    }

    /**
     * @param AMQPMessage $message
     * @throws RuntimeException
     */
    public function publish(string $messageBody): void
    {
        try {
            if ($this->producer === null) {
                throw new RuntimeException('Kafka producer is not available');
            }

            $topic = $this->producer->newTopic(static::ORDER_CREATED_TOPIC_NAME);

            $this->logger->debug(
                sprintf('Queued message with key [%s] and content [%s]', static::ORDER_CREATED_TOPIC_NAME, var_export($messageBody, true))
            );
            $topic->produce(RD_KAFKA_PARTITION_UA, 0, $messageBody);
        } catch (Exception $e) {
            $logMessage = sprintf(
                "[%s()] - Exception %s[%s] while publishing message [%s]",
                __METHOD__,
                get_class($e),
                $e->getMessage(),
                var_export($messageBody, true)
            );

            $this->logger->err($logMessage);

            throw $e;
        }
    }

    /**
     * @throws Exception
     */
    public function closeConnection(): void
    {
        $this->logger->debug('closing connection...');
        try {
            if ($this->producer !== null) {
                $this->producer->flush(30000);
            }
        } catch (Exception $e) {
            $this->logger->err(sprintf('[%s()] - Unable to close producer', __METHOD__));
            throw $e;
        }
    }
}
