<?php

declare(strict_types=1);

namespace SystemTest;

use Laminas\Log\LoggerInterface;
use PHPUnit\Framework\TestCase;
use System\ConfigProvider;
use System\Log\Factory\LogFactory;

class ConfigProviderTest extends TestCase
{
    public function testInvoke(): void
    {
        $configProvider = new ConfigProvider();
        $this->assertEquals(
            [
                'dependencies' => [
                    'factories' => [
                        LoggerInterface::class => LogFactory::class,
                    ],
                ]
            ],
            $configProvider()
        );
    }
}
