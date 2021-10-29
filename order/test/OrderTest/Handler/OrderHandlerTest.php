<?php

declare(strict_types=1);

namespace OrderTest\Handler;

use DateTime;
use JsonException;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Log\LoggerInterface;
use Order\Entity\OrderEntity;
use Order\Exception\RuntimeException;
use Order\Handler\OrderHandler;
use Order\Middleware\SaveOrderToDatabaseMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class OrderHandlerTest extends TestCase
{
    public function testExceptionThrownOnRequestWithoutAttribute(): void
    {
        $this->expectException(RuntimeException::class);

        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock->expects($this->once())->method('err');

        $orderHandler = new OrderHandler($loggerMock);
        $orderHandler->handle(
            $this->createMock(ServerRequestInterface::class)
        );
    }

    /**
     * @throws RuntimeException
     * @throws JsonException
     */
    public function testResponse(): void
    {
        $mockOrderEntity = new OrderEntity();
        $mockOrderEntity->setId(10);
        $mockOrderEntity->setAmount(100);
        $mockOrderEntity->setCurrency('USD');
        $mockOrderEntity->setInsertedAt(new DateTime('2021-01-01'));

        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock->expects($this->never())->method('err');

        $requestMock = $this->createMock(ServerRequestInterface::class);
        $requestMock->expects($this->once())->method('getAttribute')
            ->with(SaveOrderToDatabaseMiddleware::CREATED_ORDER)->willReturn($mockOrderEntity);

        $orderHandler = new OrderHandler($loggerMock);

        $response = $orderHandler->handle($requestMock);

        $json = json_decode((string)$response->getBody(), true, 512, JSON_THROW_ON_ERROR);

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertEquals(201, $response->getStatusCode());
        self::assertEquals(
            [
                'id' => 10,
                'amount' => 100,
                'currency' => 'USD',
                'insertedAt' => '2021-01-01 00:00:00'
            ],
            $json
        );
    }
}
