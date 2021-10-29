<?php

declare(strict_types=1);

namespace SystemTest\Log\Factory;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Laminas\Log\Logger;
use PHPUnit\Framework\TestCase;
use System\Log\Factory\LogFactory;

class LogFactoryTest extends TestCase
{
    /**
     * @throws ContainerException
     */
    public function testInvoke(): void
    {
        $containerMock = $this->createMock(ContainerInterface::class);
        $containerMock->expects($this->never())->method('get');
        $factory = new LogFactory();
        $this->assertInstanceOf(Logger::class, $factory($containerMock, ''));
    }
}
