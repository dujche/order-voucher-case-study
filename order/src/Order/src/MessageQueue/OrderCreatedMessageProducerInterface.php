<?php

declare(strict_types=1);

namespace Order\MessageQueue;

interface OrderCreatedMessageProducerInterface
{
  public function publish(string $messageBody): void;

  public function closeConnection(): void;
}
