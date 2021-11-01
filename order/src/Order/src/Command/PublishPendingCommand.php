<?php

declare(strict_types=1);

namespace Order\Command;

use Exception;
use JsonException;
use Laminas\Log\LoggerInterface;
use Order\Entity\OrderEntity;
use Order\MessageQueue\OrderCreatedMessageProducer;
use Order\Service\OrderService;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PublishPendingCommand extends Command
{
    private OrderService $orderService;

    private OrderCreatedMessageProducer $producer;

    private LoggerInterface $logger;

    public function __construct(
        OrderService $orderService,
        OrderCreatedMessageProducer $producer,
        LoggerInterface $logger,
        string $name = null
    ) {
        parent::__construct($name);

        $this->orderService = $orderService;
        $this->producer = $producer;
        $this->logger = $logger;
    }

    /**
     * @throws JsonException
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pendingOrders = $this->orderService->getAllUnpublished();

        if (count($pendingOrders) < 1) {
            $this->logger->debug('No orders pending for publishing. Exiting.');
            return 0;
        }

        /** @var OrderEntity $pendingOrder */
        foreach ($pendingOrders as $pendingOrder)
        {
            $this->logger->debug(sprintf('Call publish for order[%s]', $pendingOrder->getId()));

            $messageBody = json_encode(
                [
                    'id' => $pendingOrder->getId(),
                    'amount' => $pendingOrder->getAmount(),
                    'currency' => $pendingOrder->getCurrency(),
                ],
                JSON_THROW_ON_ERROR
            );

            $message = new AMQPMessage(
                $messageBody,
                [
                    'delivery_mode' => OrderCreatedMessageProducer::DELIVERY_MODE,
                ]
            );

            $this->producer->publish($message, OrderCreatedMessageProducer::ORDER_CREATED_CHANNEL_NAME);

            $this->orderService->setPublished($pendingOrder->getId());
        }

        $this->producer->closeConnection();

        return 0;
    }
}
