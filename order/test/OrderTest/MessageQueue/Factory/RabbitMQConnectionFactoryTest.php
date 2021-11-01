<?php

declare(strict_types=1);

namespace OrderTest\MessageQueue\Factory;

use Exception;
use Interop\Container\ContainerInterface;
use Laminas\Log\LoggerInterface;
use Order\Exception\InvalidArgumentException;
use Order\Exception\RuntimeException;
use Order\MessageQueue\Factory\RabbitMQConnectionFactory;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;


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
        $this->logger = $this->createMock(LoggerInterface::class);

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
     */
    public function testInvokeRabbitMQConfigIsMissing(array $config): void
    {
        $this->container->expects($this->exactly(2))
            ->method('get')->withConsecutive(
                [ LoggerInterface::class ],
                [ 'config' ]
            )->willReturnOnConsecutiveCalls($this->logger, $config);

        $this->assertNull($this->factory->__invoke($this->container, ''));
    }


    /**
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function testInvokeFailToBuildConnectionDueToIncorrectCredentials(): void
    {
        $this->container->expects($this->exactly(2))
            ->method('get')->withConsecutive(
                [ LoggerInterface::class ],
                [ 'config' ]
            )->willReturnOnConsecutiveCalls($this->logger, $this->config);

        $this->assertNull($this->factory->__invoke($this->container, ''));
    }

    /**
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function testInvokeFailToBuildConnection(): void
    {
        $this->container->expects($this->exactly(2))
            ->method('get')->withConsecutive(
                [ LoggerInterface::class ],
                [ 'config' ]
            )->willReturnOnConsecutiveCalls($this->logger, $this->config);

        $factory = $this->getMockBuilder(RabbitMQConnectionFactory::class)->onlyMethods(['createConnection'])->getMock();
        $factory->expects($this->once())->method('createConnection')->willReturn(null);
        $this->assertNull($factory->__invoke($this->container, ''));
    }

    /**
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function testInvokeSucceeds(): void
    {
        $this->container->expects($this->exactly(2))
            ->method('get')->withConsecutive(
                [ LoggerInterface::class ],
                [ 'config' ]
            )->willReturnOnConsecutiveCalls($this->logger, $this->config);

        $factory = $this->getMockBuilder(RabbitMQConnectionFactory::class)->onlyMethods(['createConnection'])->getMock();
        $factory->expects($this->once())->method('createConnection')->willReturn($this->createMock(AMQPStreamConnection::class));

        $factory->__invoke($this->container, '');
    }
}
