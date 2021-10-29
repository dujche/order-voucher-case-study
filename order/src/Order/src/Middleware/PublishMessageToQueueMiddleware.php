<?php

declare(strict_types=1);

namespace Order\Middleware;

use JsonException;
use Laminas\Log\LoggerInterface;
use Order\Entity\OrderEntity;
use Order\Exception\RuntimeException;
use Order\MessageQueue\OrderCreatedMessageProducer;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class PublishMessageToQueueMiddleware implements MiddlewareInterface
{
    private OrderCreatedMessageProducer $producer;

    private LoggerInterface $logger;

    public function __construct(OrderCreatedMessageProducer $producer, LoggerInterface $logger)
    {
        $this->producer = $producer;
        $this->logger = $logger;
    }

    /**
     * @throws RuntimeException
     * @throws JsonException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var OrderEntity|null $createdOrder */
        $createdOrder = $request->getAttribute(SaveOrderToDatabaseMiddleware::CREATED_ORDER);

        $this->publishToQueue($createdOrder);

        return $handler->handle($request);
    }

    /**
     * @param OrderEntity|null $createdOrder
     * @throws JsonException|RuntimeException
     */
    private function publishToQueue(?OrderEntity $createdOrder): void
    {
        if ($createdOrder === null) {
            $this->logger->err('Created order missing in the request object.');
            throw new RuntimeException();
        }

        $this->logger->debug(sprintf('Call publish for order[%s]', $createdOrder->getId()));

        $messageBody = json_encode(
            [
                'id' => $createdOrder->getId(),
                'amount' => $createdOrder->getAmount(),
                'currency' => $createdOrder->getCurrency(),
                'redeliverCount' => 0
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
    }
}
