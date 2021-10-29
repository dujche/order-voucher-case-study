<?php

declare(strict_types=1);

namespace Order;

use Laminas\Log\LoggerInterface;
use Laminas\ServiceManager\AbstractFactory\ConfigAbstractFactory;
use Order\Entity\OrderEntityHydrator;
use Order\Error\CustomErrorHandlerMiddleware;
use Order\Filter\CreateOrderPayloadFilter;
use Order\Handler\OrderHandler;
use Order\Middleware\CreateOrderPayloadValidationMiddleware;
use Order\Middleware\SaveOrderToDatabaseMiddleware;
use Order\Service\OrderService;
use Order\Table\OrderTable;

/**
 * The configuration provider for the App module
 *
 * @see https://docs.laminas.dev/laminas-component-installer/
 */
class ConfigProvider
{
    /**
     * Returns the configuration array
     *
     * To add a bit of a structure, each section is defined in a separate
     * method which returns an array with its configuration.
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            ConfigAbstractFactory::class => $this->getConfigAbstractFactories(),
        ];
    }

    /**
     * Returns the container dependencies
     */
    public function getDependencies(): array
    {
        return [
            'invokables' => [
                OrderEntityHydrator::class,
                CreateOrderPayloadFilter::class,
            ],
            'factories' => [
                OrderHandler::class => ConfigAbstractFactory::class,
                OrderService::class => ConfigAbstractFactory::class,
                OrderTable::class => ConfigAbstractFactory::class,
                SaveOrderToDatabaseMiddleware::class => ConfigAbstractFactory::class,
                CreateOrderPayloadValidationMiddleware::class => ConfigAbstractFactory::class,
                CustomErrorHandlerMiddleware::class => ConfigAbstractFactory::class,
            ],
        ];
    }

    private function getConfigAbstractFactories(): array
    {
        return [
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
            ],
            CreateOrderPayloadValidationMiddleware::class => [
                CreateOrderPayloadFilter::class,
                LoggerInterface::class,
            ],
            CustomErrorHandlerMiddleware::class => [
                LoggerInterface::class
            ]
        ];
    }
}
