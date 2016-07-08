<?php

namespace Pckg\Concept\ChainOfResponsibility;

/**
 * Class Next
 *
 * @package Pckg\Concept\ChainOfResponsibility
 */
trait Next
{

    /**
     * @var null
     */
    protected $next = null;

    /**
     * @param $next
     *
     * @return $this
     */
    public function setNext($next)
    {
        $this->next = $next;

        return $this;
    }
}