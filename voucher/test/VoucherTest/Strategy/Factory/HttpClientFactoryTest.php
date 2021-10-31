<?php

declare(strict_types=1);

namespace VoucherTest\Strategy\Factory;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use PHPUnit\Framework\TestCase;
use Voucher\Strategy\Factory\HttpClientFactory;

class HttpClientFactoryTest extends TestCase
{
    /**
     * @throws ContainerException
     */
    public function testInvoke(): void
    {
        $containerMock = $this->createMock(ContainerInterface::class);
        $containerMock->expects($this->never())->method('get');

        $instance = new HttpClientFactory();
        $instance($containerMock, '');
    }
}
