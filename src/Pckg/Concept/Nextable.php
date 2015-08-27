<?php

namespace Pckg\Concept;

trait Nextable
{

    protected $next = null;

    public function next()
    {
        return true;
    }

    public function setNext(AbstractChainOfReponsibility $next)
    {
        $this->next = $next;

        return $this;
    }

}