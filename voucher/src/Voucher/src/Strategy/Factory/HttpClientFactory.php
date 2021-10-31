<?php

declare(strict_types=1);

namespace Voucher\Strategy\Factory;

use GuzzleHttp\Client;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class HttpClientFactory implements FactoryInterface
{

    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): Client
    {
        return new Client(
            [
                'base_uri' => 'https://exchange-rates.abstractapi.com'
            ]
        );
    }
}
