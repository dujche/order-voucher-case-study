<?php

declare(strict_types=1);

namespace Order\Entity;

use Laminas\Hydrator\ClassMethodsHydrator;
use Laminas\Hydrator\Strategy\DateTimeFormatterStrategy;

class OrderEntityHydrator extends ClassMethodsHydrator
{
    public function __construct(bool $underscoreSeparatedKeys = true, bool $methodExistsCheck = false)
    {
        parent::__construct($underscoreSeparatedKeys, $methodExistsCheck);

        $dateTimeStrategy = new DateTimeFormatterStrategy('Y-m-d H:i:s');

        $this->addStrategy('insertedAt', $dateTimeStrategy);
        $this->addStrategy('publishedAt', $dateTimeStrategy);
    }
}
