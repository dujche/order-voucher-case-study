<?php

declare(strict_types=1);

namespace Voucher\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Voucher\Interfaces\ListenerInterface;

class ListenCommand extends Command
{
    private ListenerInterface $listener;

    public function __construct(ListenerInterface $listener, string $name = null)
    {
        parent::__construct($name);
        $this->listener = $listener;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Starting MQ Listener');
        $this->listener->listen();
        return 0;
    }
}
