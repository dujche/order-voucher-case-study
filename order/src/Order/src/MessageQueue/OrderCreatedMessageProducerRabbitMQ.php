<?php

declare(strict_types=1);

namespace Order\MessageQueue;

use Exception;
use Laminas\Log\LoggerInterface;
use Order\Exception\RuntimeException;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class OrderCreatedMessageProducerRabbitMQ implements OrderCreatedMessageProducerInterface
{
    public const DELIVERY_MODE = 2;
    public const ORDER_CREATED_CHANNEL_NAME = 'order_created';

    private LoggerInterface $logger;

    private ?AMQPStreamConnection $connection;

    private ?AMQPChannel $channel;

    public function __construct(
        LoggerInterface $logger,
        ?AMQPStreamConnection $connection,
        ?AMQPChannel $channel
    ) {
        $this->logger = $logger;
        $this->connection = $connection;
        $this->channel = $channel;
    }

    /**
     * @param AMQPMessage $message
     * @param string $routingKey
     * @throws RuntimeException
     */
    public function publish(string $messageBody): void
    {
        try {
            if ($this->channel === null) {
                throw new RuntimeException('Rabbit MQ channel is not available');
            }

            $message = new AMQPMessage(
                $messageBody,
                [
                    'delivery_mode' => static::DELIVERY_MODE,
                ]
            );

            $this->channel->basic_publish($message, '', static::ORDER_CREATED_CHANNEL_NAME);
            $this->logger->debug(
                sprintf('Queued message with key [%s] and content [%s]', static::ORDER_CREATED_CHANNEL_NAME, var_export($message->body, true))
            );
        } catch (Exception $e) {
            $logMessage = sprintf(
                "[%s()] - Exception %s[%s] while publishing message [%s]",
                __METHOD__,
                get_class($e),
                $e->getMessage(),
                var_export($message->body, true)
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
        $this->logger->debug('closing channel...');

        try {
            if ($this->channel !== null) {
                $this->channel->close();
            }
        } catch (Exception $e) {
            $this->logger->err(sprintf('[%s()] - Unable to close channel', __METHOD__));
            throw $e;
        }

        $this->logger->debug('closing connection...');
        try {
            if ($this->connection !== null) {
                $this->connection->close();
            }
        } catch (Exception $e) {
            $this->logger->err(sprintf('[%s()] - Unable to close connection', __METHOD__));
            throw $e;
        }
    }
}
