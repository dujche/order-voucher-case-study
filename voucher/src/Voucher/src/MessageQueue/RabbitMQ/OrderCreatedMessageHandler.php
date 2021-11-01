<?php

declare(strict_types=1);

namespace Voucher\MessageQueue\RabbitMQ;

use Exception;
use Laminas\Log\LoggerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Voucher\Interfaces\CreateVoucherStrategyInterface;
use Voucher\Interfaces\MessageHandlerInterface;
use Voucher\Interfaces\MessageParserInterface;
use Voucher\Interfaces\MessageValidatorInterface;

class OrderCreatedMessageHandler implements MessageHandlerInterface
{
    private LoggerInterface $logger;

    private MessageValidatorInterface $messageValidator;

    private CreateVoucherStrategyInterface $createVoucherStrategy;

    private MessageParserInterface $messageParser;

    public function __construct(
        LoggerInterface $logger,
        MessageValidatorInterface $messageValidator,
        CreateVoucherStrategyInterface $createVoucherStrategy,
        MessageParserInterface $messageParser
    ) {
        $this->logger = $logger;
        $this->messageValidator = $messageValidator;
        $this->createVoucherStrategy = $createVoucherStrategy;
        $this->messageParser = $messageParser;
    }

    public function handle(AMQPMessage $message): void
    {
        $messageValueObject = $this->messageParser->parseMessage($message->getBody());

        if (!$this->messageValidator->isValid($messageValueObject)) {
            $this->logger->err(sprintf("Invalid message received %s", $message->getBody()));
            $message->getChannel()->basic_nack($message->getDeliveryTag());
            return;
        }

        try {
            $createVoucherResult = $this->createVoucherStrategy->createVoucher($messageValueObject);
            if ($createVoucherResult !== null) {
                $this->logger->debug(
                    sprintf(
                        "Voucher with id %s was created for order with id %s",
                        $createVoucherResult,
                        $messageValueObject->getId()
                    )
                );
            } else {
                $this->logger->debug(
                    sprintf(
                        "No new voucher created for order with id %s",
                        $messageValueObject->getId()
                    )
                );
            }

            $message->getChannel()->basic_ack($message->getDeliveryTag());
        } catch (Exception $e) {
            $logMessage = sprintf(
                'Exception caught processing order %s : %s.',
                $messageValueObject->getId(),
                $e->getMessage()
            );

            $this->logger->err($logMessage);

            $message->getChannel()->basic_nack($message->getDeliveryTag());
        }
        unset($translatedMessage);
        gc_collect_cycles();
    }
}
