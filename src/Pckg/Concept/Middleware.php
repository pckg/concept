<?php

namespace Pckg\Concept;

trait Middleware
{

    protected $middlewareChain = [];

    public function middleware()
    {
        chain($this->middlewareChain);

        return $this;
    }

    public function addMiddleware($middleware)
    {
        $this->middlewareChain[] = $middleware;

        return $this;
    }

}