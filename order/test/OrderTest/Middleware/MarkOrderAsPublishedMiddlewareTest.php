<?php

declare(strict_types=1);

namespace OrderTest\Middleware;

use Laminas\Log\LoggerInterface;
use Order\Entity\OrderEntity;
use Order\Middleware\MarkOrderAsPublishedMiddleware;
use Order\Middleware\PublishMessageToQueueMiddleware;
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
        $requestMock = $this->createMock(ServerRequestInterface::class);
        $requestMock->expects($this->exactly(2))->method('getAttribute')
            ->withConsecutive(
                [
                    SaveOrderToDatabaseMiddleware::CREATED_ORDER
                ],
                [
                    PublishMessageToQueueMiddleware::class
                ],
            )->willReturnOnConsecutiveCalls(
                null,
                true
            );

        $requestHandlerMock = $this->createMock(RequestHandlerInterface::class);
        $requestHandlerMock->expects($this->once())->method('handle');

        $this->logger->expects($this->once())->method('err')
            ->with(
                'Caught following exception while trying to set publishedAt: Created order missing in the request object.'
            );

        $this->middleware->process(
            $requestMock,
            $requestHandlerMock
        );
    }

    public function testProcessWithoutPublishedOrderInRequest(): void
    {
        $requestMock = $this->createMock(ServerRequestInterface::class);
        $requestMock->expects($this->exactly(2))->method('getAttribute')
            ->withConsecutive(
                [
                    SaveOrderToDatabaseMiddleware::CREATED_ORDER
                ],
                [
                    PublishMessageToQueueMiddleware::class
                ],
            )->willReturnOnConsecutiveCalls(
                $this->orderEntity,
                false
            );

        $requestHandlerMock = $this->createMock(RequestHandlerInterface::class);
        $requestHandlerMock->expects($this->once())->method('handle');

        $this->logger->expects($this->once())->method('err')
            ->with(
                'Caught following exception while trying to set publishedAt: Publish to queue was not successful. Aborting DB update.'
            );

        $this->middleware->process(
            $requestMock,
            $requestHandlerMock
        );
    }

    public function testProcessWithoutSuccessInDatabase(): void
    {
        $requestMock = $this->createMock(ServerRequestInterface::class);
        $requestMock->expects($this->exactly(2))->method('getAttribute')
            ->withConsecutive(
                [
                    SaveOrderToDatabaseMiddleware::CREATED_ORDER
                ],
                [
                    PublishMessageToQueueMiddleware::class
                ],
            )->willReturnOnConsecutiveCalls(
                $this->orderEntity,
                true
            );

        $requestHandlerMock = $this->createMock(RequestHandlerInterface::class);
        $requestHandlerMock->expects($this->once())->method('handle')->with($requestMock);

        $this->logger->expects($this->once())->method('err')
            ->with(
                'Caught following exception while trying to set publishedAt: No records in the database were updated'
            );

        $this->orderService->expects($this->once())->method('setPublished')->with(5)->willReturn(false);

        $this->middleware->process(
            $requestMock,
            $requestHandlerMock
        );
    }

    public function testProcessWithSuccessInDatabase(): void
    {
        $requestMock = $this->createMock(ServerRequestInterface::class);
        $requestMock->expects($this->exactly(2))->method('getAttribute')
            ->withConsecutive(
                [
                    SaveOrderToDatabaseMiddleware::CREATED_ORDER
                ],
                [
                    PublishMessageToQueueMiddleware::class
                ],
            )->willReturnOnConsecutiveCalls(
                $this->orderEntity,
                true
            );

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
