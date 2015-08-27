<?php

namespace Pckg\Concept;

trait Runnable
{

    protected $runChain = [];

    public function run()
    {
        chain($this->runChain);

        return $this;
    }

}