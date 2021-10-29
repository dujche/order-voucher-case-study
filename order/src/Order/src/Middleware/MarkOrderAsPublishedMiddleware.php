<?php

declare(strict_types=1);

namespace Order\Middleware;

use JsonException;
use Laminas\Log\LoggerInterface;
use Order\Entity\OrderEntity;
use Order\Exception\RuntimeException;
use Order\Service\OrderService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

class MarkOrderAsPublishedMiddleware implements MiddlewareInterface
{
    private OrderService $orderService;

    private LoggerInterface $logger;

    public function __construct(OrderService $orderService, LoggerInterface $logger)
    {
        $this->orderService = $orderService;
        $this->logger = $logger;
    }

    /**
     * @throws RuntimeException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var OrderEntity|null $createdOrder */
        $createdOrder = $request->getAttribute(SaveOrderToDatabaseMiddleware::CREATED_ORDER);

        $this->markOrderAsPublished($createdOrder);

        return $handler->handle($request);
    }

    /**
     * @param OrderEntity|null $createdOrder
     * @throws RuntimeException
     */
    private function markOrderAsPublished(?OrderEntity $createdOrder): void
    {
        if ($createdOrder === null) {
            $this->logger->err('Created order missing in the request object.');
            throw new RuntimeException();
        }

        try {
            $updateResult = $this->orderService->setPublished($createdOrder->getId());
            if ($updateResult === false) {
                throw new RuntimeException('No records in the database were updated');
            }
        } catch(Throwable $exception) {
            $this->logger->err('Caught following exception while trying to set publishedAt: ' . $exception->getMessage());
        }
    }
}
