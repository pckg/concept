<?php

namespace Pckg\Concept;

use Pckg\Concept\ChainOfResponsibility\Next;

abstract class AbstractChainOfReponsibility
{

    use Next, CanHandle;

    protected $runMethod = null;

}