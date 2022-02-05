<?php

declare(strict_types=1);

namespace Voucher\MessageQueue\Kafka;

use Exception;
use Laminas\Log\LoggerInterface;
use Voucher\Interfaces\ConsumerInterface;
use Voucher\Interfaces\ListenerInterface;

class OrderCreatedListener implements ListenerInterface
{
    private LoggerInterface $logger;

    private ConsumerInterface $consumer;

    /**
     * Constructor.
     * @param LoggerInterface $logger
     * @param ConsumerInterface $consumer
     */
    public function __construct(
        LoggerInterface $logger,
        ConsumerInterface $consumer
    ) {
        $this->logger = $logger;
        $this->consumer = $consumer;
    }

    /**
     * @throws Exception
     */
    public function listen(): void
    {
        try {
            $this->logger->debug(sprintf('%s() - Running...', __METHOD__));
            while (true) {
                $this->consumer->consume();
            }
            //$this->logger->debug('closing channel...');
            //$this->consumer->close();
        } catch (Exception $e) {
            $this->logger->err(
                sprintf('%s() - ERR: An error occurred %s:', __METHOD__, $e->getMessage())
            );
            throw $e;
        }
    }
}
