<?php

declare(strict_types=1);

namespace VoucherTest\Strategy;

use GuzzleHttp\Client;
use Laminas\Log\LoggerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Voucher\Strategy\CurrencyExchangeRateFetcher;

class CurrencyExchangeRateFetcherTest extends TestCase
{
    public function testFetchRate(): void
    {
        $streamInterfaceMock = $this->createMock(StreamInterface::class);
        $streamInterfaceMock->expects($this->once())->method('getContents')
            ->willReturn(
                json_encode(
                    [
                        'exchange_rates' =>
                            [
                                'USD' => 1.11
                            ]
                    ],
                    JSON_THROW_ON_ERROR
                )
            );

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->expects($this->once())->method('getBody')->willReturn($streamInterfaceMock);

        $clientMock = $this->createMock(Client::class);
        $clientMock->expects($this->once())->method('request')->willReturn($responseMock);

        $instance = new CurrencyExchangeRateFetcher(
            [
                'exchange-rates' => [
                    'api-key' => 'foo'
                ]
            ],
            $clientMock,
            $this->createMock(LoggerInterface::class)
        );

        $instance->fetchRate('EUR', 'USD');
    }
}
