<?php

declare(strict_types=1);

namespace VoucherTest\MessageQueue\RabbitMQ;

use Exception;
use JsonException;
use Laminas\Log\LoggerInterface;
use PHPUnit\Framework\TestCase;
use Voucher\MessageQueue\RabbitMQ\OrderCreatedMessageParser;
use Voucher\Strategy\CurrencyExchangeRateFetcher;

class OrderCreatedMessageParserTest extends TestCase
{
    private CurrencyExchangeRateFetcher $currencyExchangeRateFetcher;

    private LoggerInterface $logger;

    private OrderCreatedMessageParser $messageParser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->currencyExchangeRateFetcher = $this->createMock(CurrencyExchangeRateFetcher::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->messageParser = new OrderCreatedMessageParser($this->logger, $this->currencyExchangeRateFetcher);
    }

    /**
     * @throws JsonException
     */
    public function testIsValidWithNullAsMessage(): void
    {
        $this->currencyExchangeRateFetcher->expects($this->never())->method('fetchRate');

        $result = $this->messageParser->parseMessage(null);

        $this->assertFalse($result->isValid());
    }

    /**
     * @throws JsonException
     */
    public function testIsValidWithoutCurrency(): void
    {
        $this->currencyExchangeRateFetcher->expects($this->never())->method('fetchRate');

        $result = $this->messageParser->parseMessage(
            json_encode(['id' => 50, 'amount' => 2000], JSON_THROW_ON_ERROR),
        );

        $this->assertFalse($result->isValid());
    }

    /**
     * @throws JsonException
     */
    public function testIsValid(): void
    {
        $this->currencyExchangeRateFetcher->expects($this->never())->method('fetchRate');

        $result = $this->messageParser->parseMessage(
            json_encode(['id' => 50, 'amount' => 2000, 'currency' => 'EUR'], JSON_THROW_ON_ERROR),
        );

        $this->assertTrue($result->isValid());
    }

    /**
     * @throws JsonException
     */
    public function testIsValidInDifferentCurrency(): void
    {
        $this->currencyExchangeRateFetcher->expects($this->once())->method('fetchRate')
            ->with('EUR', 'USD')->willReturn(1.1);

        $result = $this->messageParser->parseMessage(
            json_encode(['id' => 50, 'amount' => 2000, 'currency' => 'USD'], JSON_THROW_ON_ERROR),
        );

        $this->assertTrue($result->isValid());
        $this->assertSame("1818", $result->getMoney()->getAmount());
    }

    /**
     * @throws JsonException
     */
    public function testIsValidInDifferentCurrencyThrowsExceptionInRate(): void
    {
        $this->currencyExchangeRateFetcher->expects($this->once())->method('fetchRate')
            ->with('EUR', 'USD')->willThrowException(new Exception('foo'));

        $this->logger->expects($this->once())->method('err')
            ->with('Caught Exception when fetching exchange rate: foo');

        $result = $this->messageParser->parseMessage(
            json_encode(['id' => 50, 'amount' => 2000, 'currency' => 'USD'], JSON_THROW_ON_ERROR),
        );

        $this->assertFalse($result->isValid());
    }
}
