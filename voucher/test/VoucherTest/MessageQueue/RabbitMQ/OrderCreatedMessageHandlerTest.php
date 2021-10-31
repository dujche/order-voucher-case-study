<?php

declare(strict_types=1);

namespace VoucherTest\MessageQueue\RabbitMQ;

use Exception;
use JsonException;
use Laminas\Log\LoggerInterface;
use Money\Money;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\TestCase;
use Voucher\Interfaces\CreateVoucherStrategyInterface;
use Voucher\Interfaces\MessageHandlerInterface;
use Voucher\Interfaces\MessageParserInterface;
use Voucher\Interfaces\MessageValidatorInterface;
use Voucher\MessageQueue\RabbitMQ\OrderCreatedMessageHandler;
use Voucher\Strategy\QueueMessageValueObject;

class OrderCreatedMessageHandlerTest extends TestCase
{
    private LoggerInterface $logger;

    private MessageValidatorInterface $messageValidator;

    private CreateVoucherStrategyInterface $createVoucherStrategy;

    private MessageHandlerInterface $messageHandler;

    private AMQPMessage $messageMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logger = $this->createMock(LoggerInterface::class);
        $this->messageValidator = $this->createMock(MessageValidatorInterface::class);
        $this->createVoucherStrategy = $this->createMock(CreateVoucherStrategyInterface::class);

        $messageValueObject = new QueueMessageValueObject(150, Money::EUR(10000));

        $messageParserMock = $this->createMock(MessageParserInterface::class);
        $messageParserMock->expects($this->once())->method('parseMessage')
            ->willReturn($messageValueObject);

        $messageBody = ['id' => 150];

        $this->messageMock = $this->createMock(AMQPMessage::class);
        $this->messageMock->method('getBody')
            ->willReturn(json_encode($messageBody, JSON_THROW_ON_ERROR));

        $this->messageHandler = new OrderCreatedMessageHandler(
            $this->logger,
            $this->messageValidator,
            $this->createVoucherStrategy,
            $messageParserMock
        );
    }

    /**
     * @throws JsonException
     */
    public function testWithInvalidMessage(): void
    {
        $this->messageValidator->expects($this->once())->method('isValid')->willReturn(false);

        $channelMock = $this->createMock(AMQPChannel::class);
        $channelMock->expects($this->once())->method('basic_nack');

        $this->messageMock->expects($this->once())->method('getChannel')
            ->willReturn($channelMock);

        $this->messageHandler->handle($this->messageMock);
    }

    /**
     * @throws JsonException
     */
    public function testWithExceptionInStrategyImplementation(): void
    {
        $this->messageValidator->expects($this->once())->method('isValid')->willReturn(true);
        $this->logger->expects($this->once())->method('err')->with('Exception caught processing order 150 : foo.');

        $this->createVoucherStrategy->expects($this->once())->method('createVoucher')
            ->willThrowException(new Exception('foo'));

        $channelMock = $this->createMock(AMQPChannel::class);
        $channelMock->expects($this->once())->method('basic_nack');

        $this->messageMock->expects($this->once())->method('getChannel')
            ->willReturn($channelMock);

        $this->messageHandler->handle($this->messageMock);
    }

    /**
     * @throws JsonException
     */
    public function testHandleSuccessfullyWithoutVoucherCreated(): void
    {
        $this->messageValidator->expects($this->once())->method('isValid')->willReturn(true);

        $this->logger->expects($this->never())->method('err');
        $this->logger->expects($this->once())->method('debug')->with('No new voucher created for order with id 150');

        $this->createVoucherStrategy->expects($this->once())->method('createVoucher')->willReturn(null);

        $channelMock = $this->createMock(AMQPChannel::class);
        $channelMock->expects($this->once())->method('basic_ack');

        $this->messageMock->expects($this->once())->method('getChannel')
            ->willReturn($channelMock);

        $this->messageHandler->handle($this->messageMock);
    }

    public function testHandleSuccessfullyWithVoucherCreated(): void
    {
        $this->messageValidator->expects($this->once())->method('isValid')->willReturn(true);

        $this->logger->expects($this->never())->method('err');
        $this->logger->expects($this->once())->method('debug')
            ->with('Voucher with id 88 was created for order with id 150');

        $this->createVoucherStrategy->expects($this->once())->method('createVoucher')
            ->willReturn(88);

        $channelMock = $this->createMock(AMQPChannel::class);
        $channelMock->expects($this->once())->method('basic_ack');

        $this->messageMock->expects($this->once())->method('getChannel')
            ->willReturn($channelMock);

        $this->messageHandler->handle($this->messageMock);
    }
}
