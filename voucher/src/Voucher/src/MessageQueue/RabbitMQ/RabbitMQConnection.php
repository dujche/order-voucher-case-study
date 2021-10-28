<?php

declare(strict_types=1);

namespace Voucher\MessageQueue\RabbitMQ;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class RabbitMQConnection
{
    private AMQPStreamConnection $connection;

    public function __construct(AMQPStreamConnection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return AMQPStreamConnection
     */
    public function getConnection(): AMQPStreamConnection
    {
        return $this->connection;
    }

    /**
     * @param string $channelName
     * @return AMQPChannel
     */
    public function buildChannel(string $channelName): AMQPChannel
    {
        $channel = $this->connection->channel();
        $channel->queue_declare($channelName, false, true, false, false);
        $channel->basic_qos(null, 1, null);

        return $channel;
    }
}
