<?php

namespace Pckg\Concept;

use Pckg\Reflect;

class ChainOfResponsibility
{

    protected $chains = [];
    protected $firstChain = null;

    protected $runMethod = 'execute';
    protected $args = [];

    public function __construct($chains = [])
    {
        $this->chains = $chains;
    }

    public function runChains()
    {
        if (!$this->chains) {
            return null;
        }

        if ($this->firstChain) {
            $firstChain = $this->firstChain;
        } else {
            $firstChain = new AbstractObject();
        }

        foreach (array_reverse($this->chains) AS $chain) {
            if (is_string($chain)) {
                $chain = Reflect::create($chain);
            }

            $firstChain = $chain->setNext($firstChain);
        }

        return Reflect::method($firstChain, $this->runMethod, $this->args);
    }

    public function setRunMethod($runMethod)
    {
        $this->runMethod = $runMethod;

        return $this;
    }

    public function setFirstChain($firstChain)
    {
        $this->firstChain = $firstChain;

        return $this;
    }

    public function getFirstChain()
    {
        return $this->firstChain;
    }

    public function setArgs($args)
    {
        $this->args = $args;

        return $this;
    }

}