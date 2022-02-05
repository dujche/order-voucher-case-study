<?php

declare(strict_types=1);

namespace Voucher\MessageQueue\Kafka;

use Voucher\Interfaces\ConsumerInterface;
use Voucher\Interfaces\MessageHandlerInterface;
use Laminas\Log\LoggerInterface;

class OrderCreatedConsumer implements ConsumerInterface
{
    private \RdKafka\KafkaConsumer $kafkaConsumer;

    private MessageHandlerInterface $messageHandler;

    private LoggerInterface $logger;

    public function __construct(
        \RdKafka\KafkaConsumer $kafkaConsumer,
        MessageHandlerInterface $messageHandler,
        LoggerInterface $logger
    ) {
        $this->kafkaConsumer = $kafkaConsumer;
        $this->messageHandler = $messageHandler;
        $this->logger = $logger;
    }

    public function consume(): void
    {
        $message = $this->kafkaConsumer->consume(10 * 1000);
        switch ($message->err) {
            case RD_KAFKA_RESP_ERR_NO_ERROR:
                $this->messageHandler->handle($message);
                break;
            case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                $this->logger->info("No more messages; will wait for more");
                break;
            case RD_KAFKA_RESP_ERR__TIMED_OUT:
                $this->logger->info("Timed out");
                break;
            default:
                $this->logger->err($message->errstr());
                throw new \Exception($message->errstr(), $message->err);
                break;
        }
    }
}
