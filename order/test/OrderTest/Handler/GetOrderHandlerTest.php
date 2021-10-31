<?php

declare(strict_types=1);

namespace OrderTest\Handler;

use DateTime;
use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Order\Entity\OrderEntity;
use Order\Handler\GetOrderHandler;
use Order\Service\OrderService;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class GetOrderHandlerTest extends TestCase
{
    private OrderService $orderService;

    private GetOrderHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderService = $this->createMock(OrderService::class);
        $this->handler = new GetOrderHandler($this->orderService);
    }

    public function testHandleOnGetAllRoute(): void
    {
        $this->orderService->expects($this->once())->method('getAll')->willReturn([]);
        $result = $this->handler->handle($this->createMock(ServerRequestInterface::class));

        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals('[]', $result->getBody()->getContents());
    }

    public function testHandleOnGetSingleRouteWithoutResult(): void
    {
        $requestMock = $this->createMock(ServerRequestInterface::class);
        $requestMock->expects($this->once())->method('getAttribute')->with('id')->willReturn(10);

        $this->orderService->expects($this->once())->method('getById')->with(10)->willReturn(null);
        $result = $this->handler->handle($requestMock);

        $this->assertInstanceOf(EmptyResponse::class, $result);
        $this->assertEquals(404, $result->getStatusCode());
    }

    public function testHandleOnGetSingleRouteWithResult(): void
    {
        $orderEntity = new OrderEntity();
        $orderEntity->setId(10);
        $orderEntity->setAmount(1000);
        $orderEntity->setCurrency('EUR');
        $orderEntity->setInsertedAt(new DateTime('2021-01-01'));

        $requestMock = $this->createMock(ServerRequestInterface::class);
        $requestMock->expects($this->once())->method('getAttribute')->with('id')->willReturn(10);

        $this->orderService->expects($this->once())->method('getById')->with(10)->willReturn($orderEntity);
        $result = $this->handler->handle($requestMock);

        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals(
            '{"id":10,"amount":1000,"currency":"EUR","insertedAt":"2021-01-01 00:00:00","publishedAt":null}',
            $result->getBody()->getContents()
        );
    }
}
