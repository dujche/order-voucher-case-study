<?php

declare(strict_types=1);

namespace Order\Filter;

use Laminas\Filter\StringTrim;
use Laminas\InputFilter\InputFilter;
use Laminas\Validator\Callback;
use Laminas\Validator\StringLength;
use Money\Currencies\ISOCurrencies;
use Money\Currency;

class CreateOrderPayloadFilter extends InputFilter
{
    public function __construct()
    {
        $this->addAmountInput();
        $this->addCurrencyInput();
    }

    private function addAmountInput(): void
    {
        $this->add(
            [
                'name' => 'amount',
                'required' => true,
                'validators' => [
                    [
                        'name' => Callback::class,
                        'options' => [
                            'callback' => function ($amount) {
                                return is_int($amount);
                            },
                            'messages' => [
                                Callback::INVALID_VALUE => 'Amount must be integer',
                            ]
                        ]
                    ],
                ],
            ]
        );
    }

    private function addCurrencyInput(): void
    {
        $currencies = new ISOCurrencies();

        $this->add(
            [
                'name' => 'currency',
                'required' => true,
                'filters' => [
                    [
                        'name' => StringTrim::class,
                        'options' => [],
                    ],
                ],
                'validators' => [
                    [
                        'name' => StringLength::class,
                        'options' => [
                            'encoding' => 'UTF-8',
                            'min' => 3,
                            'max' => 3,
                            'messages' => [
                                StringLength::TOO_LONG => 'Currency must be exactly 3 characters long',
                                StringLength::TOO_SHORT => 'Currency must be exactly 3 characters long',
                            ]
                        ],
                    ],
                    [
                        'name' => Callback::class,
                        'options' => [
                            'callback' => function ($currency) use ($currencies) {
                                return $currencies->contains(new Currency($currency));
                            },
                            'messages' => [
                                Callback::INVALID_VALUE => 'Unknown currency code',
                            ]
                        ]
                    ]
                ],
            ]
        );
    }
}
