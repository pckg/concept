<?php

namespace Pckg\Concept;

class ChainOfResponsibility
{

    protected $chains = [];

    protected $firstChain;

    protected $runMethod;

    protected $args = [];

    public function __construct($chains = [], $runMethod = 'execute', $args = [], callable $firstChain = null)
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

        $result = $this->firstChain;

        $ok = false;
        foreach ($this->chains as $chain) {
            if (is_string($chain)) {
                $chain = Reflect::create($chain);
            }

            $ok = false;
            if (is_only_callable($chain)) {
                $result = $chain(array_merge($this->args, [
                    'next' => function() use (&$ok) {
                        return $ok = true;
                    },
                ]));

            } else {
                $result = Reflect::method($chain, $this->runMethod, array_merge($this->args, [
                    'next' => function() use (&$ok) {
                        return $ok = true;
                    },
                ]));
            }

            if (!$ok) {
                return $result;
            }
        }

        $firstChain = $this->firstChain;

        return $firstChain ? $firstChain() : true;
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