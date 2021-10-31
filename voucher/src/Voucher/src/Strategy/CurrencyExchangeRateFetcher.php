<?php

namespace Voucher\Strategy;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;
use Laminas\Log\LoggerInterface;

class CurrencyExchangeRateFetcher
{
    private array $config;

    private Client $httpClient;

    private LoggerInterface $logger;

    public function __construct(array $config, Client $httpClient, LoggerInterface $logger)
    {
        $this->config = $config;
        $this->httpClient = $httpClient;
        $this->logger = $logger;
    }

    /**
     * @throws GuzzleException
     * @throws JsonException
     */
    public function fetchRate(string $baseCurrency, string $targetCurrency): ?float
    {
        $targetEndpoint = sprintf(
            "/v1/live/?api_key=%s&base=%s&target=%s",
            $this->config['exchange-rates']['api-key'] ?? null,
            $baseCurrency,
            $targetCurrency
        );

        $response = $this->httpClient->request(
            'GET',
            $targetEndpoint
        );

        $responseContents = $response->getBody()->getContents();

        $this->logger->debug("Received response from exchange rate API: " . $responseContents);

        $decodedResult = json_decode($responseContents, true, 512, JSON_THROW_ON_ERROR);

        return $decodedResult['exchange_rates'][$targetCurrency] ?? null;
    }
}
