<?php

declare(strict_types=1);

namespace VoucherTest\MessageQueue\RabbitMQ;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Voucher\MessageQueue\RabbitMQ\RabbitMQConnection;

class RabbitMQConnectionTest extends TestCase
{
    protected RabbitMQConnection $rabbitConnection;

    /**
     * @var AMQPStreamConnection|MockObject $connection
     */
    protected $connection;

    public function setUp(): void
    {
        $this->connection = $this->setUpStreamConnection();
        $this->rabbitConnection = new RabbitMQConnection($this->connection);
    }

    /**
     * @return MockObject|AMQPStreamConnection
     */
    protected function setUpStreamConnection(): MockObject
    {
        return $this->getMockBuilder(AMQPStreamConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(RabbitMQConnection::class, $this->rabbitConnection);
    }

    public function testGetConnectionSuccess(): void
    {
        $this->assertSame($this->connection, $this->rabbitConnection->getConnection());
    }

    public function testBuildChannel(): void
    {
        $channel = $this->getMockBuilder(AMQPChannel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->connection->expects($this->once())
            ->method('channel')
            ->willReturn($channel);

        $channel->expects($this->once())
            ->method('queue_declare')
            ->with('Foo-Channel', false, true, false, false);

        $channel->expects($this->once())
            ->method('basic_qos')
            ->with(null, 1, null);

        $this->rabbitConnection->buildChannel('Foo-Channel');
    }
}
