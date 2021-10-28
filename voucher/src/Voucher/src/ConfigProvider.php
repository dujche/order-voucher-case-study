<?php

declare(strict_types=1);

namespace Voucher;

use Laminas\Log\LoggerInterface;
use Laminas\ServiceManager\AbstractFactory\ConfigAbstractFactory;
use Voucher\Command\ListenCommand;
use Voucher\MessageQueue\RabbitMQ\CreateFiveEuroVoucherStrategy;
use Voucher\MessageQueue\RabbitMQ\Factory\OrderCreatedListenerFactory;
use Voucher\MessageQueue\RabbitMQ\Factory\RabbitMQConnectionFactory;
use Voucher\MessageQueue\RabbitMQ\OrderCreatedListener;
use Voucher\MessageQueue\RabbitMQ\OrderCreatedMessageHandler;
use Voucher\MessageQueue\RabbitMQ\OrderCreatedMessageValidator;
use Voucher\MessageQueue\RabbitMQ\RabbitMQConnection;

class ConfigProvider
{
    /**
     * Return configuration for this component.
     *
     * @return array
     */
    public function __invoke(): array
    {
        return [
            'laminas-cli' => $this->getCliConfig(),
            'dependencies' => $this->getDependencyConfig(),
            ConfigAbstractFactory::class => $this->getConfigAbstractFactories(),
        ];
    }

    public function getCliConfig(): array
    {
        return [
            'commands' => [
                'voucher:listen' => ListenCommand::class,
            ],
        ];
    }

    /**
     * Return dependency mappings for this component.
     *
     * @return array
     */
    public function getDependencyConfig(): array
    {
        return [
            // Legacy Zend Framework aliases
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
        ];
    }

    private function getConfigAbstractFactories(): array
    {
        return [
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
        ];
    }
}
