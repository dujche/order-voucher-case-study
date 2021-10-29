<?php

declare(strict_types=1);

namespace OrderTest\Middleware;

use JsonException;
use Laminas\Log\LoggerInterface;
use Order\Entity\OrderEntity;
use Order\Exception\RuntimeException;
use Order\Middleware\MarkOrderAsPublishedMiddleware;
use Order\Middleware\SaveOrderToDatabaseMiddleware;
use Order\Service\OrderService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MarkOrderAsPublishedMiddlewareTest extends TestCase
{
    /**
     * @var OrderService|MockObject
     */
    private $orderService;

    /**
     * @var LoggerInterface|MockObject
     */
    private $logger;

    private MarkOrderAsPublishedMiddleware $middleware;

    private OrderEntity $orderEntity;

    public function setUp(): void
    {
        $this->orderService = $this->createMock(OrderService::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->middleware = new MarkOrderAsPublishedMiddleware(
            $this->orderService,
            $this->logger
        );

        $this->orderEntity = new OrderEntity();
        $this->orderEntity->setId(5);
        $this->orderEntity->setAmount(1030);
        $this->orderEntity->setCurrency('GBP');
    }

    public function testProcessWithoutCreatedOrderInRequest(): void
    {
        $this->expectException(RuntimeException::class);

        $requestHandlerMock = $this->createMock(RequestHandlerInterface::class);
        $requestHandlerMock->expects($this->never())->method('handle');

        $this->middleware->process(
            $this->createMock(ServerRequestInterface::class),
            $requestHandlerMock
        );
    }

    /**
     * @throws RuntimeException
     */
    public function testProcessWithoutSuccessInDatabase(): void
    {
        $requestMock = $this->createMock(ServerRequestInterface::class);
        $requestMock->expects($this->once())->method('getAttribute')
            ->with(SaveOrderToDatabaseMiddleware::CREATED_ORDER)->willReturn($this->orderEntity);

        $requestHandlerMock = $this->createMock(RequestHandlerInterface::class);
        $requestHandlerMock->expects($this->once())->method('handle')->with($requestMock);

        $this->logger->expects($this->once())->method('err')
            ->with('Caught following exception while trying to set publishedAt: No records in the database were updated');

        $this->orderService->expects($this->once())->method('setPublished')->with(5)->willReturn(false);

        $this->middleware->process(
            $requestMock,
            $requestHandlerMock
        );
    }

    /**
     * @throws RuntimeException
     */
    public function testProcessWithSuccessInDatabase(): void
    {
        $requestMock = $this->createMock(ServerRequestInterface::class);
        $requestMock->expects($this->once())->method('getAttribute')
            ->with(SaveOrderToDatabaseMiddleware::CREATED_ORDER)->willReturn($this->orderEntity);

        $requestHandlerMock = $this->createMock(RequestHandlerInterface::class);
        $requestHandlerMock->expects($this->once())->method('handle')->with($requestMock);

        $this->logger->expects($this->never())->method('err');

        $this->orderService->expects($this->once())->method('setPublished')->with(5)->willReturn(true);

        $this->middleware->process(
            $requestMock,
            $requestHandlerMock
        );
    }
}
