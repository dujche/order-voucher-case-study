<?php

declare(strict_types=1);

namespace OrderTest\Error;

use Exception;
use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Log\LoggerInterface;
use Order\Error\CustomErrorHandlerMiddleware;
use Order\Exception\RuntimeException;
use Order\Exception\ValidationException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

class CustomErrorHandlerMiddlewareTest extends TestCase
{
    public function testRuntimeException(): void
    {
        $requestMock = $this->createMock(ServerRequestInterface::class);

        $handlerMock = $this->createMock(RequestHandlerInterface::class);
        $handlerMock->expects($this->once())->method('handle')
            ->with($requestMock)->willThrowException(new RuntimeException('foo'));

        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock->expects($this->once())->method('err')
            ->with('Caught RuntimeException: foo');

        $middleware = new CustomErrorHandlerMiddleware($loggerMock);
        $response = $middleware->process($requestMock, $handlerMock);

        $this->assertInstanceOf(EmptyResponse::class, $response);
        $this->assertSame(500, $response->getStatusCode());
    }

    public function testValidationException(): void
    {
        $requestMock = $this->createMock(ServerRequestInterface::class);

        $handlerMock = $this->createMock(RequestHandlerInterface::class);
        $handlerMock->expects($this->once())->method('handle')
            ->with($requestMock)->willThrowException(new ValidationException('bar'));

        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock->expects($this->once())->method('err')
            ->with('Caught ValidationException: bar');

        $middleware = new CustomErrorHandlerMiddleware($loggerMock);
        $response = $middleware->process($requestMock, $handlerMock);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(400, $response->getStatusCode());
    }

    public function testGenericException(): void
    {
        $this->expectException(Exception::class);

        $requestMock = $this->createMock(ServerRequestInterface::class);

        $handlerMock = $this->createMock(RequestHandlerInterface::class);
        $handlerMock->expects($this->once())->method('handle')
            ->with($requestMock)->willThrowException(new Exception('bar'));

        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock->expects($this->never())->method('err');

        $middleware = new CustomErrorHandlerMiddleware($loggerMock);
        $middleware->process($requestMock, $handlerMock);
    }

    public function testNoException(): void
    {
        $requestMock = $this->createMock(ServerRequestInterface::class);

        $handlerMock = $this->createMock(RequestHandlerInterface::class);
        $handlerMock->expects($this->once())->method('handle')
            ->with($requestMock)->willReturn(new EmptyResponse(200));

        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock->expects($this->never())->method('err');

        $middleware = new CustomErrorHandlerMiddleware($loggerMock);
        $response = $middleware->process($requestMock, $handlerMock);

        $this->assertInstanceOf(EmptyResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());
    }
}
