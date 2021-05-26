<?php

namespace Test\Concept\Reflect\Resolver;

use Pckg\Concept\Event\Dispatcher;
use Pckg\Concept\Reflect\Resolver\Context;
use Pckg\Framework\Config;
use Pckg\Framework\Provider;
use Pckg\Framework\Request;
use Pckg\Framework\Response;
use Pckg\Framework\Router;
use Pckg\Framework\Test\Codeception\Cest;

class ContextCest
{
    use Cest;

    public function testCanResolveSystemDependencies()
    {
        $context = context();
        $contextResolver = new Context();
        $this->tester->assertTrue($contextResolver->canResolve(\Pckg\Framework\Helper\Context::class));
        $this->tester->assertTrue($contextResolver->canResolve(Dispatcher::class));
        $this->tester->assertTrue($contextResolver->canResolve(Router::class));
        $this->tester->assertTrue($contextResolver->canResolve(Config::class));
        $this->tester->assertTrue($contextResolver->canResolve(Request::class));
        $this->tester->assertTrue($contextResolver->canResolve(Response::class));
        $this->tester->assertFalse($contextResolver->canResolve(Provider::class));

        // aliased Context!
        $this->tester->assertEquals($context->get(\Pckg\Concept\Context::class), $contextResolver->resolve(\Pckg\Framework\Helper\Context::class));
        $this->tester->assertEquals($context->get(Dispatcher::class), $contextResolver->resolve(Dispatcher::class));
        $this->tester->assertEquals($context->get(Router::class), $contextResolver->resolve(Router::class));
        $this->tester->assertEquals($context->get(Config::class), $contextResolver->resolve(Config::class));
        $this->tester->assertEquals($context->get(Request::class), $contextResolver->resolve(Request::class));
        $this->tester->assertEquals($context->get(Response::class), $contextResolver->resolve(Response::class));
    }
}
