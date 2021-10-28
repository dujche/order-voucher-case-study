<?php

declare(strict_types=1);

namespace VoucherTest;

use Laminas\Log\LoggerInterface;
use Laminas\ServiceManager\AbstractFactory\ConfigAbstractFactory;
use PHPUnit\Framework\TestCase;
use Voucher\Command\ListenCommand;
use Voucher\ConfigProvider;
use Voucher\MessageQueue\RabbitMQ\CreateFiveEuroVoucherStrategy;
use Voucher\MessageQueue\RabbitMQ\Factory\OrderCreatedListenerFactory;
use Voucher\MessageQueue\RabbitMQ\Factory\RabbitMQConnectionFactory;
use Voucher\MessageQueue\RabbitMQ\OrderCreatedListener;
use Voucher\MessageQueue\RabbitMQ\OrderCreatedMessageHandler;
use Voucher\MessageQueue\RabbitMQ\OrderCreatedMessageValidator;
use Voucher\MessageQueue\RabbitMQ\RabbitMQConnection;

class ConfigProviderTest extends TestCase
{
    public function testInvoke(): void
    {
        $configProvider = new ConfigProvider();
        $this->assertEquals(
            [
                'laminas-cli' => [
                    'commands' => [
                        'voucher:listen' => ListenCommand::class,
                    ],
                ],
                'dependencies' => [
                    'invokables' => [
                        CreateFiveEuroVoucherStrategy::class,
                    ],
                    'factories' => [
                        RabbitMQConnection::class => RabbitMQConnectionFactory::class,
                        OrderCreatedListener::class => OrderCreatedListenerFactory::class,
                        OrderCreatedMessageValidator::class => ConfigAbstractFactory::class,
                        OrderCreatedMessageHandler::class => ConfigAbstractFactory::class,
                        ListenCommand::class => ConfigAbstractFactory::class,
                    ]
                ],
                ConfigAbstractFactory::class => [
                    OrderCreatedMessageValidator::class => [
                        LoggerInterface::class
                    ],
                    OrderCreatedMessageHandler::class => [
                        LoggerInterface::class,
                        OrderCreatedMessageValidator::class,
                        CreateFiveEuroVoucherStrategy::class,
                    ],
                    ListenCommand::class => [
                        OrderCreatedListener::class,
                    ]
                ]
            ],
            $configProvider()
        );
    }
}
