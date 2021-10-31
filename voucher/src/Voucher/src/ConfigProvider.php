<?php

declare(strict_types=1);

namespace Voucher;

use GuzzleHttp\Client;
use Laminas\Log\LoggerInterface;
use Laminas\ServiceManager\AbstractFactory\ConfigAbstractFactory;
use Voucher\Command\ListenCommand;
use Voucher\Entity\VoucherEntityHydrator;
use Voucher\Handler\GetVoucherHandler;
use Voucher\MessageQueue\RabbitMQ\Factory\OrderCreatedListenerFactory;
use Voucher\MessageQueue\RabbitMQ\Factory\RabbitMQConnectionFactory;
use Voucher\MessageQueue\RabbitMQ\OrderCreatedListener;
use Voucher\MessageQueue\RabbitMQ\OrderCreatedMessageHandler;
use Voucher\MessageQueue\RabbitMQ\OrderCreatedMessageParser;
use Voucher\MessageQueue\RabbitMQ\OrderCreatedMessageValidator;
use Voucher\MessageQueue\RabbitMQ\RabbitMQConnection;
use Voucher\Service\VoucherService;
use Voucher\Strategy\CreateFiveEuroVoucherStrategy;
use Voucher\Strategy\CurrencyExchangeRateFetcher;
use Voucher\Strategy\Factory\HttpClientFactory;
use Voucher\Table\VoucherTable;

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
                VoucherEntityHydrator::class,
                OrderCreatedMessageValidator::class,
            ],
            'factories' => [
                RabbitMQConnection::class => RabbitMQConnectionFactory::class,
                OrderCreatedListener::class => OrderCreatedListenerFactory::class,
                OrderCreatedMessageHandler::class => ConfigAbstractFactory::class,
                OrderCreatedMessageParser::class => ConfigAbstractFactory::class,
                ListenCommand::class => ConfigAbstractFactory::class,
                VoucherTable::class => ConfigAbstractFactory::class,
                VoucherService::class => ConfigAbstractFactory::class,
                CreateFiveEuroVoucherStrategy::class => ConfigAbstractFactory::class,
                CurrencyExchangeRateFetcher::class => ConfigAbstractFactory::class,
                Client::class => HttpClientFactory::class,
                GetVoucherHandler::class => ConfigAbstractFactory::class,
            ]
        ];
    }

    private function getConfigAbstractFactories(): array
    {
        return [
            OrderCreatedMessageHandler::class => [
                LoggerInterface::class,
                OrderCreatedMessageValidator::class,
                CreateFiveEuroVoucherStrategy::class,
                OrderCreatedMessageParser::class,
            ],
            OrderCreatedMessageParser::class => [
                LoggerInterface::class,
                CurrencyExchangeRateFetcher::class,
            ],
            ListenCommand::class => [
                OrderCreatedListener::class,
            ],
            VoucherTable::class => [
                'voucher-db',
                VoucherEntityHydrator::class,
                LoggerInterface::class,
            ],
            VoucherService::class => [
                VoucherTable::class,
            ],
            CreateFiveEuroVoucherStrategy::class => [
                VoucherService::class,
                LoggerInterface::class,
            ],
            CurrencyExchangeRateFetcher::class => [
                'config',
                Client::class,
                LoggerInterface::class,
            ],
            GetVoucherHandler::class => [
                VoucherService::class
            ]
        ];
    }
}
