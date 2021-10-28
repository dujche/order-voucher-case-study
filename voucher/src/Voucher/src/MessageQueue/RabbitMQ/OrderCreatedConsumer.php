<?php

declare(strict_types=1);

namespace Voucher\MessageQueue\RabbitMQ;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use Voucher\MessageQueue\Interfaces\ConsumerInterface;
use Voucher\MessageQueue\Interfaces\MessageHandlerInterface;

class OrderCreatedConsumer implements ConsumerInterface
{
    private AMQPChannel $channel;

    private MessageHandlerInterface $messageHandler;

    public function __construct(AMQPChannel $channel, MessageHandlerInterface $messageHandler)
    {
        $this->channel = $channel;
        $this->messageHandler = $messageHandler;
    }

    public function consume(): void
    {
        $messageHandler = $this->messageHandler;
        $this->channel->basic_consume(
            '',
            '',
            false,
            false,
            false,
            false,
            function (AMQPMessage $message) use ($messageHandler) {
                $messageHandler->handle($message);
            }
        );
    }
}
