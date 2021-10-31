<?php

declare(strict_types=1);

namespace VoucherTest\Command;

use Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Voucher\Command\ListenCommand;
use Voucher\Interfaces\ListenerInterface;

class ListenCommandTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testExecute(): void
    {
        $listenerMock = $this->createMock(ListenerInterface::class);
        $listenerMock->expects($this->once())->method('listen');
        $command = new ListenCommand($listenerMock);
        $command->run(
            $this->createMock(InputInterface::class),
            $this->createMock(OutputInterface::class)
        );
    }
}
