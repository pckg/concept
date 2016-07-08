<?php

namespace Pckg\Concept;

use Pckg\Concept\Reflect;

class ChainOfResponsibility
{

    protected $chains = [];

    protected $firstChain;

    protected $runMethod;

    protected $args = [];

    public function __construct($chains = [], $runMethod = 'execute', $args = [], $firstChain = null)
    {
        $this->chains = $chains;
        $this->runMethod = $runMethod;
        $this->args = $args;
        $this->firstChain = $firstChain;
    }

    /**
     * @T00D00 - this needs to be refactored without nesting ...
     * @return null
     */
    public function runChains()
    {
        if (!$this->chains) {
            return null;
        }

        $next = $this->firstChain ?: function() {
            return $this;
        };

        foreach (array_reverse($this->chains) as $chain) {
            $next = function() use ($chain, $next) {
                if (is_string($chain)) {
                    $chain = Reflect::create($chain);
                }

                if (is_callable($chain)) {
                    $result = $chain(array_merge($this->args, ['next' => $next]));

                } else {
                    $result = Reflect::method($chain, $this->runMethod, array_merge($this->args, ['next' => $next]));

                }

                return $result;
            };
        }

        //startMeasure('Chain: ' . $this->runMethod . '()');
        $result = $next();

        //stopMeasure('Chain: ' . $this->runMethod . '()');

        return $result;
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

    public function setArgs($args)
    {
        $this->args = $args;

        return $this;
    }

}