<?php

declare(strict_types=1);

namespace Voucher\MessageQueue\RabbitMQ;

use Exception;
use Laminas\Log\LoggerInterface;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Voucher\MessageQueue\Interfaces\ConsumerInterface;
use Voucher\MessageQueue\Interfaces\ListenerInterface;

class OrderCreatedListener implements ListenerInterface
{
    private LoggerInterface $logger;

    private ConsumerInterface $consumer;

    private AMQPChannel $channel;

    private AMQPStreamConnection $connection;

    /**
     * Constructor.
     * @param LoggerInterface $logger
     * @param ConsumerInterface $consumer
     */
    public function __construct(LoggerInterface $logger, ConsumerInterface $consumer, AMQPChannel $channel, AMQPStreamConnection $connection)
    {
        $this->logger = $logger;
        $this->consumer = $consumer;
        $this->channel = $channel;
        $this->connection = $connection;
    }

    /**
     * @throws Exception
     */
    public function listen(): void
    {
        try {
            $this->logger->debug(sprintf('%s() - Running...', __METHOD__));
            $this->consumer->consume();
            while (count($this->channel->callbacks)) {
                $this->channel->wait();
            }
            $this->close();
        } catch (Exception $e) {
            $this->logger->err(
                sprintf('%s() - ERR: An error occurred %s:', __METHOD__, $e->getMessage())
            );
            throw $e;
        }
    }

    /**
     * @throws Exception
     */
    private function close(): void
    {
        try {
            $this->logger->debug('closing channel...');
            $this->channel->close();
            $this->logger->debug('closing connection...');
            $this->connection->close();
        } catch (Exception $e) {
            $this->logger->err(
                sprintf('%s() - Exception while closing channel/connection: %s', __METHOD__, $e->getMessage())
            );

            throw $e;
        }
    }
}


