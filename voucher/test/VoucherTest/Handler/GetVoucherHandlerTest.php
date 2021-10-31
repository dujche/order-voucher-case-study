<?php

declare(strict_types=1);

namespace VoucherTest\Handler;

use DateTime;
use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Response\JsonResponse;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Voucher\Entity\VoucherEntity;
use Voucher\Handler\GetVoucherHandler;
use Voucher\Service\VoucherService;

class GetVoucherHandlerTest extends TestCase
{
    private VoucherService $voucherService;

    private GetVoucherHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->voucherService = $this->createMock(VoucherService::class);
        $this->handler = new GetVoucherHandler($this->voucherService);
    }

    public function testHandleOnGetAllRoute(): void
    {
        $this->voucherService->expects($this->once())->method('getAll')->willReturn([]);
        $result = $this->handler->handle($this->createMock(ServerRequestInterface::class));

        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals('[]', $result->getBody()->getContents());
    }

    public function testHandleOnGetSingleRouteWithoutResult(): void
    {
        $requestMock = $this->createMock(ServerRequestInterface::class);
        $requestMock->expects($this->once())->method('getAttribute')->with('id')->willReturn(10);

        $this->voucherService->expects($this->once())->method('getById')->with(10)->willReturn(null);
        $result = $this->handler->handle($requestMock);

        $this->assertInstanceOf(EmptyResponse::class, $result);
        $this->assertEquals(404, $result->getStatusCode());
    }

    public function testHandleOnGetSingleRouteWithResult(): void
    {
        $voucherEntity = new VoucherEntity();
        $voucherEntity->setOrderId(20);
        $voucherEntity->setId(10);
        $voucherEntity->setAmount(1000);
        $voucherEntity->setCurrency('EUR');
        $voucherEntity->setInsertedAt(new DateTime('2021-01-01'));

        $requestMock = $this->createMock(ServerRequestInterface::class);
        $requestMock->expects($this->once())->method('getAttribute')->with('id')->willReturn(10);

        $this->voucherService->expects($this->once())->method('getById')->with(10)->willReturn($voucherEntity);
        $result = $this->handler->handle($requestMock);

        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals(
            '{"id":10,"orderId":20,"amount":1000,"currency":"EUR","insertedAt":"2021-01-01 00:00:00"}',
            $result->getBody()->getContents()
        );
    }
}
