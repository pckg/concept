<?php

namespace Pckg\Concept;

use Pckg\Concept\ChainOfResponsibility\Next;

abstract class AbstractChainOfReponsibility
{
    use Next;
    use CanHandle;

    protected $runMethod = null;
}
