<?php

declare(strict_types=1);

namespace OrderTest\MessageQueue;

use Exception;
use JsonException;
use Laminas\Log\LoggerInterface;
use Order\MessageQueue\OrderCreatedMessageProducer;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OrderCreatedMessageProducerTest extends TestCase
{
    private OrderCreatedMessageProducer $producer;

    /**
     * @var LoggerInterface|MockObject $logger
     */
    private $logger;

    /**
     * @var AMQPStreamConnection|MockObject $connection
     */
    private $connection;

    /**
     * @var AMQPChannel|MockObject $channel
     */
    private $channel;

    private AMQPMessage $message;

    /**
     * @throws JsonException
     */
    public function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->connection = $this->createMock(AMQPStreamConnection::class);
        $this->channel = $this->createMock(AMQPChannel::class);

        $this->message = new AMQPMessage(
            json_encode(
                [
                    'id' => 123,
                    'amount' => 10000,
                    'currency' => 'EUR',
                    'redeliverCount' => 0,
                ],
                JSON_THROW_ON_ERROR
            )
        );

        $this->producer = new OrderCreatedMessageProducer(
            $this->logger,
            $this->connection,
            $this->channel
        );
    }

    public function testConstructor(): void
    {
        $this->assertInstanceOf(OrderCreatedMessageProducer::class, $this->producer);
    }

    public function testPublishExceptionQueueing(): void
    {
        $routingKey = 'some_route_key';

        $this->channel->expects($this->once())
            ->method('basic_publish')
            ->with(
                $this->identicalTo($this->message),
                $this->identicalTo(''),
                $this->identicalTo($routingKey)
            )
            ->willThrowException(new Exception('Foo-Exception'));

        $this->logger->expects($this->once())
            ->method('err')
            ->with(
                '[Order\MessageQueue\OrderCreatedMessageProducer::publish()] - Exception Exception[Foo-Exception] while publishing message [\'{"id":123,"amount":10000,"currency":"EUR","redeliverCount":0}\']'
            );

        $this->producer->publish($this->message, $routingKey);
    }

    public function testExceptionClosingChannel(): void
    {
        $this->logger->expects($this->once())
            ->method('debug')
            ->with('closing channel...');

        $this->channel->expects($this->once())
            ->method('close')
            ->willThrowException(new Exception('close-channel-exception'));

        $this->logger->expects($this->once())
            ->method('err')
            ->with('[Order\MessageQueue\OrderCreatedMessageProducer::closeConnection()] - Unable to close channel');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('close-channel-exception');

        $this->producer->closeConnection();
    }

    public function testExceptionClosingConnection(): void
    {
        $this->logger->expects($this->exactly(2))
            ->method('debug')
            ->withConsecutive(
                ['closing channel...'],
                ['closing connection...']
            );

        $this->channel->expects($this->once())
            ->method('close');

        $this->connection->expects($this->once())
            ->method('close')
            ->willThrowException(new Exception('close-connection-exception'));

        $this->logger->expects($this->once())
            ->method('err')
            ->with('[Order\MessageQueue\OrderCreatedMessageProducer::closeConnection()] - Unable to close connection');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('close-connection-exception');

        $this->producer->closeConnection();
    }

    public function testPublishSuccess(): void
    {
        $routingKey = 'some_route_key';

        $this->channel->expects($this->once())
            ->method('basic_publish')
            ->with(
                $this->identicalTo($this->message),
                $this->identicalTo(''),
                $this->identicalTo($routingKey)
            );

        $this->logger->expects($this->once())
            ->method('debug')
            ->with(
                $this->identicalTo(
                    'Queued message with key [some_route_key] and content [\'{"id":123,"amount":10000,"currency":"EUR","redeliverCount":0}\']'
                )
            );

        $this->logger->expects($this->never())
            ->method('err');

        $this->producer->publish($this->message, $routingKey);
    }

    /**
     * @throws Exception
     */
    public function testClosingConnectionSuccess(): void
    {
        $this->logger->expects($this->exactly(2))
            ->method('debug')
            ->withConsecutive(
                ['closing channel...'],
                ['closing connection...']
            );

        $this->channel->expects($this->once())
            ->method('close');

        $this->connection->expects($this->once())
            ->method('close');

        $this->logger->expects($this->never())
            ->method('err');

        $this->producer->closeConnection();
    }
}
