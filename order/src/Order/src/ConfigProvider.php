<?php

declare(strict_types=1);

namespace Order;

use Laminas\Log\LoggerInterface;
use Laminas\ServiceManager\AbstractFactory\ConfigAbstractFactory;
use Order\Command\PublishPendingCommand;
use Order\Entity\OrderEntityHydrator;
use Order\Error\CustomErrorHandlerMiddleware;
use Order\Filter\CreateOrderPayloadFilter;
use Order\Handler\GetOrderHandler;
use Order\Handler\PostOrderHandler;
use Order\MessageQueue\Factory\OrderCreatedMessageProducerFactory;
use Order\MessageQueue\Factory\RabbitMQConnectionFactory;
use Order\MessageQueue\OrderCreatedMessageProducer;
use Order\MessageQueue\RabbitMQConnection;
use Order\Middleware\CreateOrderPayloadValidationMiddleware;
use Order\Middleware\MarkOrderAsPublishedMiddleware;
use Order\Middleware\PublishMessageToQueueMiddleware;
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
            'laminas-cli' => $this->getCliConfig(),
            'dependencies' => $this->getDependencies(),
            ConfigAbstractFactory::class => $this->getConfigAbstractFactories(),
        ];
    }

    public function getCliConfig(): array
    {
        return [
            'commands' => [
                'order:publish:pending' => PublishPendingCommand::class,
            ],
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
                PostOrderHandler::class => ConfigAbstractFactory::class,
                GetOrderHandler::class => ConfigAbstractFactory::class,
                OrderService::class => ConfigAbstractFactory::class,
                OrderTable::class => ConfigAbstractFactory::class,
                SaveOrderToDatabaseMiddleware::class => ConfigAbstractFactory::class,
                CreateOrderPayloadValidationMiddleware::class => ConfigAbstractFactory::class,
                CustomErrorHandlerMiddleware::class => ConfigAbstractFactory::class,
                OrderCreatedMessageProducer::class => OrderCreatedMessageProducerFactory::class,
                RabbitMQConnection::class => RabbitMQConnectionFactory::class,
                PublishMessageToQueueMiddleware::class => ConfigAbstractFactory::class,
                MarkOrderAsPublishedMiddleware::class => ConfigAbstractFactory::class,
                PublishPendingCommand::class => ConfigAbstractFactory::class,
            ],
        ];
    }

    private function getConfigAbstractFactories(): array
    {
        return [
            PostOrderHandler::class => [
                LoggerInterface::class
            ],
            GetOrderHandler::class => [
                OrderService::class,
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
            ],
            PublishMessageToQueueMiddleware::class => [
                OrderCreatedMessageProducer::class,
                LoggerInterface::class,
            ],
            MarkOrderAsPublishedMiddleware::class => [
                OrderService::class,
                LoggerInterface::class,
            ],
            PublishPendingCommand::class => [
                OrderService::class,
                OrderCreatedMessageProducer::class,
                LoggerInterface::class,
            ]
        ];
    }
}
