<?php

namespace Pckg\Concept;

trait Initializable
{

    protected $initChain = [];

    public function init()
    {
        chain($this->initChain);

        return $this;
    }

}