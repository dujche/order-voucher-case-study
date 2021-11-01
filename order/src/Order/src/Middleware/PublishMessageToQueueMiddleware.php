<?php

declare(strict_types=1);

namespace Order\Middleware;

use Exception;
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

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var OrderEntity|null $createdOrder */
        $createdOrder = $request->getAttribute(SaveOrderToDatabaseMiddleware::CREATED_ORDER);

        try {
            $this->publishToQueue($createdOrder);
            $request = $request->withAttribute(static::class, true);
        } catch (Exception $exception) {
            $this->logger->err(
                'Caught following exception while trying to publish to message queue: ' . $exception->getMessage()
            );
        }

        return $handler->handle($request);
    }

    /**
     * @param OrderEntity|null $createdOrder
     * @throws JsonException|RuntimeException
     * @throws Exception
     */
    private function publishToQueue(?OrderEntity $createdOrder): void
    {
        if ($createdOrder === null) {
            throw new RuntimeException('Created order missing in the request object.');
        }

        $this->logger->debug(sprintf('Call publish for order[%s]', $createdOrder->getId()));

        $messageBody = json_encode(
            [
                'id' => $createdOrder->getId(),
                'amount' => $createdOrder->getAmount(),
                'currency' => $createdOrder->getCurrency(),
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

        $this->producer->closeConnection();
    }
}
