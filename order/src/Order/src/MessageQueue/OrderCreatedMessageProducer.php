<?php

declare(strict_types=1);

namespace Order\MessageQueue;

use Exception;
use Laminas\Log\LoggerInterface;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class OrderCreatedMessageProducer
{
    public const DELIVERY_MODE = 2;
    public const ORDER_CREATED_CHANNEL_NAME = 'order_created';

    private LoggerInterface $logger;

    private AMQPStreamConnection $connection;

    private AMQPChannel $channel;

    public function __construct(
        LoggerInterface $logger,
        AMQPStreamConnection $connection,
        AMQPChannel $channel
    ) {
        $this->logger = $logger;
        $this->connection = $connection;
        $this->channel = $channel;
    }

    /**
     * @param AMQPMessage $message
     * @param string $routingKey
     */
    public function publish(AMQPMessage $message, string $routingKey): void
    {
        try {
            $this->channel->basic_publish($message, '', $routingKey);
            $this->logger->debug(
                sprintf('Queued message with key [%s] and content [%s]', $routingKey, var_export($message->body, true))
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
        }
    }

    /**
     * @throws Exception
     */
    public function closeConnection(): void
    {
        $this->logger->debug('closing channel...');

        try {
            $this->channel->close();
        } catch (Exception $e) {
            $this->logger->err(sprintf('[%s()] - Unable to close channel', __METHOD__));
            throw $e;
        }

        $this->logger->debug('closing connection...');
        try {
            $this->connection->close();
        } catch (Exception $e) {
            $this->logger->err(sprintf('[%s()] - Unable to close connection', __METHOD__));
            throw $e;
        }
    }

}
