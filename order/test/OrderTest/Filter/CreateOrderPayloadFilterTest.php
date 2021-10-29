<?php

declare(strict_types=1);

namespace OrderTest\Filter;

use Order\Filter\CreateOrderPayloadFilter;
use PHPUnit\Framework\TestCase;

class CreateOrderPayloadFilterTest extends TestCase
{
    public function testConstructor(): void
    {
        $instance = new CreateOrderPayloadFilter();

        $this->assertTrue($instance->has('amount'));
        $this->assertTrue($instance->has('currency'));
    }

    /**
     * @dataProvider isValidDataProvider
     */
    public function testIsValid(bool $expectedResult, array $samplePayload): void
    {
        $instance = new CreateOrderPayloadFilter();
        $instance->setData($samplePayload);

        $this->assertSame($expectedResult, $instance->isValid());
    }

    public function isValidDataProvider(): array
    {
        return [
            'faulty payload' => [
                false,
                [
                    'foo' => 'bar'
                ]
            ],
            'amount as string' => [
                false,
                [
                    'amount' => '100',
                    'currency' => 'EUR'
                ]
            ],
            'currency too long' => [
                false,
                [
                    'amount' => 100,
                    'currency' => 'EURA'
                ]
            ],
            'valid payload' => [
                true,
                [
                    'amount' => 100,
                    'currency' => 'EUR'
                ]
            ]
        ];
    }
}
