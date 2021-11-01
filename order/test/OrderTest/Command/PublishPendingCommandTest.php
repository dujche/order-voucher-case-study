<?php

declare(strict_types=1);

namespace OrderTest\Command;

use DateTime;
use Laminas\Log\LoggerInterface;
use Order\Command\PublishPendingCommand;
use Order\Entity\OrderEntity;
use Order\MessageQueue\OrderCreatedMessageProducer;
use Order\Service\OrderService;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PublishPendingCommandTest extends TestCase
{
    private OrderService $orderService;

    private OrderCreatedMessageProducer $producer;

    private LoggerInterface $logger;

    private PublishPendingCommand $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderService = $this->createMock(OrderService::class);

        $this->logger = $this->createMock(LoggerInterface::class);

        $this->producer = $this->createMock(OrderCreatedMessageProducer::class);

        $this->command = new PublishPendingCommand($this->orderService, $this->producer, $this->logger);
    }

    public function testWithNoPendingOrders(): void
    {
        $this->orderService->expects($this->once())->method('getAllUnpublished')->willReturn([]);

        $this->logger->expects($this->once())->method('debug')->with('No orders pending for publishing. Exiting.');

        $this->producer->expects($this->never())->method('publish');

        $this->producer->expects($this->never())->method('closeConnection');

        $this->command->run(
            $this->createMock(InputInterface::class),
            $this->createMock(OutputInterface::class)
        );
    }

    public function testWithPendingOrders(): void
    {
        $orderEntity = new OrderEntity();
        $orderEntity->setId(10);
        $orderEntity->setAmount(1000);
        $orderEntity->setCurrency('EUR');
        $orderEntity->setInsertedAt(new DateTime('2021-01-01'));

        $this->orderService->expects($this->once())->method('getAllUnpublished')->willReturn([$orderEntity]);

        $this->orderService->expects($this->once())->method('setPublished')->with(10);

        $this->logger->expects($this->once())->method('debug')->with('Call publish for order[10]');

        $this->producer->expects($this->once())->method('publish')
            ->with(
                $this->callback(function (AMQPMessage $message) {
                    return '{"id":10,"amount":1000,"currency":"EUR"}' === $message->getBody();
                }),
                OrderCreatedMessageProducer::ORDER_CREATED_CHANNEL_NAME
            );

        $this->producer->expects($this->once())->method('closeConnection');

        $this->command->run(
            $this->createMock(InputInterface::class),
            $this->createMock(OutputInterface::class)
        );
    }
}
