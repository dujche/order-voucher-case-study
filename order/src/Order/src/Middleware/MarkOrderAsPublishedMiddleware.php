<?php

declare(strict_types=1);

namespace Order\Middleware;

use Exception;
use Laminas\Log\LoggerInterface;
use Order\Entity\OrderEntity;
use Order\Exception\RuntimeException;
use Order\Service\OrderService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MarkOrderAsPublishedMiddleware implements MiddlewareInterface
{
    private OrderService $orderService;

    private LoggerInterface $logger;

    public function __construct(OrderService $orderService, LoggerInterface $logger)
    {
        $this->orderService = $orderService;
        $this->logger = $logger;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var OrderEntity|null $createdOrder */
        $createdOrder = $request->getAttribute(SaveOrderToDatabaseMiddleware::CREATED_ORDER);

        $publishedToQueue = $request->getAttribute(PublishMessageToQueueMiddleware::class, false);

        try {
            $this->markOrderAsPublished($createdOrder, $publishedToQueue);
        } catch (Exception $exception) {
            $this->logger->err(
                'Caught following exception while trying to set publishedAt: ' . $exception->getMessage()
            );
        }

        return $handler->handle($request);
    }

    /**
     * @param OrderEntity|null $createdOrder
     * @throws RuntimeException
     */
    private function markOrderAsPublished(?OrderEntity $createdOrder, bool $publishedToQueue): void
    {
        if ($createdOrder === null) {
            throw new RuntimeException('Created order missing in the request object.');
        }

        if ($publishedToQueue === false) {
            throw new RuntimeException('Publish to queue was not successful. Aborting DB update.');
        }

        $updateResult = $this->orderService->setPublished($createdOrder->getId());
        if ($updateResult === false) {
            throw new RuntimeException('No records in the database were updated');
        }
    }
}
