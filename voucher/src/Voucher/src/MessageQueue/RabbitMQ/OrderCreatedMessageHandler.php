<?php

declare(strict_types=1);

namespace Voucher\MessageQueue\RabbitMQ;

use Exception;
use JsonException;
use Laminas\Log\LoggerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Voucher\MessageQueue\Interfaces\CreateVoucherStrategyInterface;
use Voucher\MessageQueue\Interfaces\MessageHandlerInterface;
use Voucher\MessageQueue\Interfaces\MessageValidatorInterface;

class OrderCreatedMessageHandler implements MessageHandlerInterface
{
    private LoggerInterface $logger;

    private MessageValidatorInterface $messageValidator;

    private CreateVoucherStrategyInterface $createVoucherStrategy;

    public function __construct(
        LoggerInterface $logger,
        MessageValidatorInterface $messageValidator,
        CreateVoucherStrategyInterface $createVoucherStrategy
    ) {
        $this->logger = $logger;
        $this->messageValidator = $messageValidator;
        $this->createVoucherStrategy = $createVoucherStrategy;
    }

    /**
     * @throws JsonException
     */
    public function handle(AMQPMessage $message): void
    {
        $translatedMessage = json_decode($message->getBody(), true, 512, JSON_THROW_ON_ERROR);

        if (!$this->messageValidator->isValid($translatedMessage)) {
            $message->getChannel()->basic_nack($message->getDeliveryTag());
            return;
        }

        try {
            $createVoucherResult = $this->createVoucherStrategy->createVoucher($translatedMessage);
            if ($createVoucherResult !== null) {
                $this->logger->debug(sprintf("Voucher with id %s was created for order with id %s", $createVoucherResult, $translatedMessage['id']));
            } else {
                $this->logger->debug(sprintf("No new voucher created for order with id %s", $translatedMessage['id']));
            }

            $message->getChannel()->basic_ack($message->getDeliveryTag());
        } catch (Exception $e) {
            $logMessage = sprintf(
                'Exception caught processing order %s : %s.',
                $translatedMessage['id'],
                $e->getMessage()
            );

            $this->logger->err($logMessage);

            $message->getChannel()->basic_nack($message->getDeliveryTag());
        }
        unset($translatedMessage);
        gc_collect_cycles();
    }
}
