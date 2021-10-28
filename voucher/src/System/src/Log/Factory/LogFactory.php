<?php

declare(strict_types=1);

namespace System\Log\Factory;

use Interop\Container\ContainerInterface;
use Laminas\Log\Logger;
use Laminas\Log\LoggerInterface;
use Laminas\Log\Writer\Stream;
use Laminas\ServiceManager\Factory\FactoryInterface;

class LogFactory implements FactoryInterface
{

    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): LoggerInterface
    {
        $logger = new Logger();
        $logger->addWriter(new Stream('php://stdout'));

        return $logger;
    }
}
