<?php

declare(strict_types=1);

namespace Order\Handler;

use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Log\LoggerInterface;
use Order\Service\OrderService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class GetOrderHandler implements RequestHandlerInterface
{
    private OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $single = $request->getAttribute('id');

        if ($single === null) {
            return new JsonResponse(
                $this->orderService->getAll()
            );
        }

        $orderEntity = $this->orderService->getById((int) $single);

        return $orderEntity ? new JsonResponse($orderEntity->toArray()) : new EmptyResponse(404);
    }
}
