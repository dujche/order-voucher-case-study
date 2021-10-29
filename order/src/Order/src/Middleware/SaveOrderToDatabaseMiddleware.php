<?php

declare(strict_types=1);

namespace Order\Middleware;

use Laminas\Log\LoggerInterface;
use Order\Entity\OrderEntity;
use Order\Exception\RuntimeException;
use Order\Service\OrderService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SaveOrderToDatabaseMiddleware implements MiddlewareInterface
{
    public const CREATED_ORDER = 'Created Order';

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
        $saveResult = $this->performSave($request);
        if ($saveResult === null) {
            $this->logger->err('Inserting order into database failed.');
            throw new RuntimeException();
        }

        return $handler->handle($request->withAttribute(static::CREATED_ORDER, $saveResult));
    }

    /**
     * @param ServerRequestInterface $request
     */
    private function performSave(ServerRequestInterface $request): ?OrderEntity
    {
        $post = $request->getParsedBody();
        $order = new OrderEntity();
        $order->setCurrency($post['currency']);
        $order->setAmount($post['amount']);

        return $this->orderService->add($order) ? $order : null;
    }
}
