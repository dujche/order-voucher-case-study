<?php

declare(strict_types=1);

namespace OrderTest;

use Laminas\Log\LoggerInterface;
use Laminas\ServiceManager\AbstractFactory\ConfigAbstractFactory;
use Order\ConfigProvider;
use Order\Entity\OrderEntityHydrator;
use Order\Handler\OrderHandler;
use Order\Middleware\SaveOrderToDatabaseMiddleware;
use Order\Service\OrderService;
use Order\Table\OrderTable;
use PHPUnit\Framework\TestCase;

class ConfigProviderTest extends TestCase
{
    public function testInvoke(): void
    {
        $configProvider = new ConfigProvider();
        $this->assertEquals(
            [
                'dependencies' => [
                    'invokables' => [
                        OrderEntityHydrator::class,
                    ],
                    'factories' => [
                        OrderHandler::class => ConfigAbstractFactory::class,
                        OrderService::class => ConfigAbstractFactory::class,
                        OrderTable::class => ConfigAbstractFactory::class,
                        SaveOrderToDatabaseMiddleware::class => ConfigAbstractFactory::class,
                    ]
                ],
                ConfigAbstractFactory::class => [
                    OrderHandler::class => [
                        LoggerInterface::class
                    ],
                    OrderService::class => [
                        OrderTable::class
                    ],
                    OrderTable::class => [
                        'order-db',
                        OrderEntityHydrator::class,
                        LoggerInterface::class
                    ],
                    SaveOrderToDatabaseMiddleware::class => [
                        OrderService::class,
                        LoggerInterface::class,
                    ]
                ]
            ],
            $configProvider()
        );
    }
}
