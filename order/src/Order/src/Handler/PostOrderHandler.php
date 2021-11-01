<?php

declare(strict_types=1);

namespace Order\Handler;

use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Log\LoggerInterface;
use Order\Entity\OrderEntity;
use Order\Exception\RuntimeException;
use Order\Middleware\SaveOrderToDatabaseMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function DeepCopy\deep_copy;

class PostOrderHandler implements RequestHandlerInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @throws RuntimeException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var OrderEntity|null $createdOrder */
        $createdOrder = $request->getAttribute(SaveOrderToDatabaseMiddleware::CREATED_ORDER);

        if ($createdOrder === null) {
            $this->logger->err('Created order missing in the request object.');
            throw new RuntimeException();
        }

        return new JsonResponse(
            [
                'id' => $createdOrder->getId(),
                'amount' => $createdOrder->getAmount(),
                'currency' => $createdOrder->getCurrency(),
                'insertedAt' => $createdOrder->getInsertedAt()->format('Y-m-d H:i:s')
            ],
            201
        );
    }
}
