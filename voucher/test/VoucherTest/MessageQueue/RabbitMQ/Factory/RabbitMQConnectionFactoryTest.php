<?php

declare(strict_types=1);

namespace VoucherTest\MessageQueue\RabbitMQ\Factory;

use Exception;
use Interop\Container\ContainerInterface;
use InvalidArgumentException;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Voucher\MessageQueue\RabbitMQ\Factory\RabbitMQConnectionFactory;


class RabbitMQConnectionFactoryTest extends TestCase
{
    /**
     * @var MockObject|ContainerInterface
     */
    protected $container;

    protected RabbitMQConnectionFactory $factory;

    protected array $config;

    protected LoggerInterface $logger;

    public function setUp(): void
    {
        $this->config = $this->setUpHostConfig();
        $this->container = $this->setUpContainer();

        $this->factory = new RabbitMQConnectionFactory();
    }

    protected function setUpContainer(): MockObject
    {
        return $this->createMock(ContainerInterface::class);
    }

    protected function setUpHostConfig(): array
    {
        return [
            'mq' => [
                'nodeConfigs' => [
                    'node0' => [
                        'host' => 'rabbitmq',
                        'port' => '5672',
                        'username' => 'foo',
                        'password' => 'bar',
                        'vhost' => '/epg/pg',
                    ],
                ],
            ],
        ];
    }

    public function providerMissConfigurationTests(): array
    {
        return [
            'Key "mq" missing' => [
                [],
            ],
            'Key "mq" empty' => [
                ['mq' => []],
            ],
            'Key "mq" not an array' => [
                [
                    'mq' => 'foo',
                ],
            ],
        ];
    }

    /**
     * @dataProvider providerMissConfigurationTests
     * @throws RuntimeException|Exception
     */
    public function testInvokeRabbitMQConfigIsMissing(array $config): void
    {
        $this->container->expects($this->once())->method('get')->with('config')->willReturn($config);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid RabbitMQ configuration.');

        $this->factory->__invoke($this->container, '');
    }


    /**
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function testInvokeFailToBuildConnectionDueToIncorrectCredentials(): void
    {
        $this->expectException(Exception::class);

        $this->container->expects($this->once())
            ->method('get')->with('config')->willReturn($this->config);

        $this->factory->__invoke($this->container, '');
    }

    /**
     * @throws InvalidArgumentException|Exception
     */
    public function testInvokeFailToBuildConnection(): void
    {
        $this->expectException(RuntimeException::class);

        $this->container->expects($this->once())
            ->method('get')->with('config')->willReturn($this->config);

        $factory = $this->getMockBuilder(RabbitMQConnectionFactory::class)->onlyMethods(['createConnection'])->getMock();
        $factory->expects($this->once())->method('createConnection')->willReturn(null);
        $factory->__invoke($this->container, '');
    }

    /**
     * @throws RuntimeException
     * @throws InvalidArgumentException|Exception
     */
    public function testInvokeSucceeds(): void
    {
        $this->container->expects($this->once())
            ->method('get')->with('config')->willReturn($this->config);

        $factory = $this->getMockBuilder(RabbitMQConnectionFactory::class)->onlyMethods(['createConnection'])->getMock();
        $factory->expects($this->once())->method('createConnection')->willReturn($this->createMock(AMQPStreamConnection::class));

        $factory->__invoke($this->container, '');
    }
}
