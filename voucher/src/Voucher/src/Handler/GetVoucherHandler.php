<?php

declare(strict_types=1);

namespace Voucher\Handler;

use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Voucher\Service\VoucherService;

class GetVoucherHandler implements RequestHandlerInterface
{
    private VoucherService $voucherService;

    public function __construct(VoucherService $voucherService)
    {
        $this->voucherService = $voucherService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $single = $request->getAttribute('id');

        if ($single === null) {
            return new JsonResponse(
                $this->voucherService->getAll()
            );
        }

        $orderEntity = $this->voucherService->getById((int) $single);

        return $orderEntity ? new JsonResponse($orderEntity->toArray()) : new EmptyResponse(404);
    }
}
