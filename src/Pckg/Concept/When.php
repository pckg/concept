<?php

namespace Pckg\Concept;

trait When
{
    
    public function when($condition, callable $callback)
    {
        if ($condition) {
            $callback($this);
        }

        return $this;
    }
}
