<?php

declare(strict_types=1);

namespace OrderTest\Middleware;

use Laminas\Log\LoggerInterface;
use Order\Entity\OrderEntity;
use Order\Exception\RuntimeException;
use Order\Middleware\SaveOrderToDatabaseMiddleware;
use Order\Service\OrderService;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SaveOrderToDatabaseMiddlewareTest extends TestCase
{
    public function testExceptionThrownOnRequestWithoutAttribute(): void
    {
        $this->expectException(RuntimeException::class);

        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock->expects($this->once())->method('err');

        $serviceMock = $this->createMock(OrderService::class);
        $serviceMock->expects($this->once())->method('add')->willReturn(false);

        $requestMock = $this->createMock(ServerRequestInterface::class);
        $requestMock->expects($this->once())->method('getParsedBody')
            ->willReturn(['amount' => 100, 'currency' => 'USD']);

        $saveOrderToDatabaseMiddleware = new SaveOrderToDatabaseMiddleware($serviceMock, $loggerMock);
        $saveOrderToDatabaseMiddleware->process(
            $requestMock,
            $this->createMock(RequestHandlerInterface::class)
        );
    }

    /**
     * @throws RuntimeException
     */
    public function testProcess(): void
    {
        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock->expects($this->never())->method('err');

        $serviceMock = $this->createMock(OrderService::class);
        $serviceMock->expects($this->once())->method('add')->willReturn(true);

        $requestMock = $this->createMock(ServerRequestInterface::class);
        $requestMock->expects($this->once())->method('getParsedBody')
            ->willReturn(['amount' => 100, 'currency' => 'USD']);
        $requestMock->expects($this->once())->method('withAttribute')
            ->with(
                SaveOrderToDatabaseMiddleware::CREATED_ORDER,
                $this->callback(function($param) {
                    return $param instanceof OrderEntity &&
                        $param->getAmount() === 100 &&
                        $param->getCurrency() === 'USD';
                })
            )->willReturnSelf();

        $saveOrderToDatabaseMiddleware = new SaveOrderToDatabaseMiddleware($serviceMock, $loggerMock);
        $saveOrderToDatabaseMiddleware->process(
            $requestMock,
            $this->createMock(RequestHandlerInterface::class)
        );

    }
}
