<?php

declare(strict_types=1);

namespace Voucher\MessageQueue\RabbitMQ;

use Exception;
use JsonException;
use Laminas\Log\LoggerInterface;
use Money\Converter;
use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Exchange\FixedExchange;
use Money\Exchange\ReversedCurrenciesExchange;
use Money\Money;
use Voucher\Interfaces\MessageParserInterface;
use Voucher\Strategy\CurrencyExchangeRateFetcher;
use Voucher\Strategy\QueueMessageValueObject;

class OrderCreatedMessageParser implements MessageParserInterface
{
    private LoggerInterface $logger;

    private CurrencyExchangeRateFetcher $currencyExchangeRateFetcher;

    public function __construct(
        LoggerInterface $logger,
        CurrencyExchangeRateFetcher $currencyExchangeRateFetcher
    ) {
        $this->logger = $logger;
        $this->currencyExchangeRateFetcher = $currencyExchangeRateFetcher;
    }

    /**
     * @throws JsonException
     */
    public function parseMessage(?string $message): QueueMessageValueObject
    {
        if ($message === null) {
            return new QueueMessageValueObject();
        }

        $messageBodyAsArray = json_decode($message, true, 512, JSON_THROW_ON_ERROR);

        $id = $messageBodyAsArray['id'] ?? null;
        $money = null;

        if (!empty($messageBodyAsArray['amount'] ?? null) && !empty($messageBodyAsArray['currency'] ?? null)) {
            $money = $messageBodyAsArray['currency'] === 'EUR' ?
                Money::EUR($messageBodyAsArray['amount']) :
                $this->convertAmountToEur(
                    $this->currencyExchangeRateFetcher,
                    $this->logger,
                    $messageBodyAsArray['amount'],
                    $messageBodyAsArray['currency']
                );
        }

        return new QueueMessageValueObject($id, $money);
    }

    private function convertAmountToEur(
        CurrencyExchangeRateFetcher $rateFetcher,
        LoggerInterface $logger,
        int $originalAmount,
        string $originalCurrency
    ): ?Money {
        try {
            $rate = $rateFetcher->fetchRate('EUR', $originalCurrency);
        } catch (Exception $ex) {
            $logger->err("Caught Exception when fetching exchange rate: " . $ex->getMessage());
            return null;
        }

        $exchange = new ReversedCurrenciesExchange(
            new FixedExchange(
                [
                    'EUR' => [
                        $originalCurrency => $rate
                    ]

                ]
            )
        );

        $converter = new Converter(new ISOCurrencies(), $exchange);
        $originalMoney = new Money($originalAmount, new Currency($originalCurrency));
        return $converter->convert($originalMoney, new Currency('EUR'));
    }
}
