<?php

namespace Pckg\Concept\Event;

use Pckg\Concept\ChainOfResponsibility\Next;

abstract class AbstractEvent
{

    use Next;

    protected $handlers = [];

    protected $name;

    public function getName()
    {
        return $this->name;
    }

}