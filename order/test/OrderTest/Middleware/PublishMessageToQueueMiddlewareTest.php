<?php

declare(strict_types=1);

namespace OrderTest\Middleware;

use JsonException;
use Laminas\Log\LoggerInterface;
use Order\Entity\OrderEntity;
use Order\Exception\RuntimeException;
use Order\MessageQueue\OrderCreatedMessageProducer;
use Order\Middleware\PublishMessageToQueueMiddleware;
use Order\Middleware\SaveOrderToDatabaseMiddleware;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class PublishMessageToQueueMiddlewareTest extends TestCase
{
    /**
     * @var OrderCreatedMessageProducer|MockObject
     */
    private $producer;

    /**
     * @var LoggerInterface|MockObject
     */
    private $logger;

    private PublishMessageToQueueMiddleware $middleware;

    public function setUp(): void
    {
        $this->producer = $this->createMock(OrderCreatedMessageProducer::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->middleware = new PublishMessageToQueueMiddleware(
            $this->producer,
            $this->logger
        );
    }

    public function testProcessWithoutCreatedOrderInRequest(): void
    {
        $this->logger->expects($this->once())->method('err')
            ->with('Caught following exception while trying to publish to message queue: Created order missing in the request object.');

        $requestHandlerMock = $this->createMock(RequestHandlerInterface::class);
        $requestHandlerMock->expects($this->once())->method('handle');

        $this->middleware->process(
            $this->createMock(ServerRequestInterface::class),
            $requestHandlerMock
        );
    }

    public function testProcess(): void
    {
        $orderEntity = new OrderEntity();
        $orderEntity->setId(5);
        $orderEntity->setAmount(1030);
        $orderEntity->setCurrency('GBP');

        $requestMock = $this->createMock(ServerRequestInterface::class);
        $requestMock->expects($this->once())->method('getAttribute')
            ->with(SaveOrderToDatabaseMiddleware::CREATED_ORDER)->willReturn($orderEntity);

        $requestMock->expects($this->once())->method('withAttribute')
            ->with(PublishMessageToQueueMiddleware::class, true)->willReturnSelf();

        $requestHandlerMock = $this->createMock(RequestHandlerInterface::class);
        $requestHandlerMock->expects($this->once())->method('handle')->with($requestMock);

        $this->logger->expects($this->once())->method('debug');

        $this->producer->expects($this->once())->method('publish')
            ->with(
                $this->callback(static function(AMQPMessage $message) {
                    return $message->getBody() === '{"id":5,"amount":1030,"currency":"GBP"}';
                }),
                OrderCreatedMessageProducer::ORDER_CREATED_CHANNEL_NAME
            );

        $this->middleware->process(
            $requestMock,
            $requestHandlerMock
        );
    }
}
