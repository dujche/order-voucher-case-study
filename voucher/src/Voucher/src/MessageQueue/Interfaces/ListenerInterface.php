<?php

namespace Voucher\MessageQueue\Interfaces;

interface ListenerInterface
{
    public function listen(): void;
}
