<?php

declare(strict_types=1);

namespace OrderTest;

use Laminas\Log\LoggerInterface;
use Laminas\ServiceManager\AbstractFactory\ConfigAbstractFactory;
use Order\Command\PublishPendingCommand;
use Order\ConfigProvider;
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
use PHPUnit\Framework\TestCase;

class ConfigProviderTest extends TestCase
{
    public function testInvoke(): void
    {
        $configProvider = new ConfigProvider();
        $this->assertEquals(
            [
                'laminas-cli' => [
                    'commands' => [
                        'order:publish:pending' => PublishPendingCommand::class,
                    ]
                ],
                'dependencies' => [
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
                ],
                ConfigAbstractFactory::class => [
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
                ]
            ],
            $configProvider()
        );
    }
}
