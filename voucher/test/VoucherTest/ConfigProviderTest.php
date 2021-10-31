<?php

declare(strict_types=1);

namespace VoucherTest;

use GuzzleHttp\Client;
use Laminas\Log\LoggerInterface;
use Laminas\ServiceManager\AbstractFactory\ConfigAbstractFactory;
use PHPUnit\Framework\TestCase;
use Voucher\Command\ListenCommand;
use Voucher\ConfigProvider;
use Voucher\Entity\VoucherEntityHydrator;
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
                    ]
                ],
                ConfigAbstractFactory::class => [
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
                    ]
                ]
            ],
            $configProvider()
        );
    }
}
