<?php

namespace Pckg\Concept;

trait CanHandle
{
    protected $methods = [];

    public function canHandle($method)
    {
        return method_exists($this, $method);
    }

    public function getMethods()
    {
        return $this->methods;
    }

    protected function initOverloadMethods()
    {
        $this->methods = $this->methods ?? [];
    }

    protected function mergeOverloadMethods($arrMethods)
    {
        $this->methods = array_unique(array_merge($this->methods ?? [], $arrMethods));
    }
}
