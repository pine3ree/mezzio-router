<?php

/**
 * @see       https://github.com/mezzio/mezzio-router for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-router/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-router/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace MezzioTest\Router\Middleware;

use Mezzio\Router\Exception\MissingDependencyException;
use Mezzio\Router\Middleware\ImplicitHeadMiddleware;
use Mezzio\Router\Middleware\ImplicitHeadMiddlewareFactory;
use Mezzio\Router\RouterInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\StreamInterface;

class ImplicitHeadMiddlewareFactoryTest extends TestCase
{
    /** @var ContainerInterface|ObjectProphecy */
    private $container;

    /** @var ImplicitHeadMiddlewareFactory */
    private $factory;

    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->factory = new ImplicitHeadMiddlewareFactory();
    }

    public function testFactoryRaisesExceptionIfRouterInterfaceServiceIsMissing()
    {
        $this->container->has(RouterInterface::class)->willReturn(false);
        $this->container->has(\Zend\Expressive\Router\RouterInterface::class)->willReturn(false);

        $this->expectException(MissingDependencyException::class);
        ($this->factory)($this->container->reveal());
    }

    public function testFactoryRaisesExceptionIfStreamFactoryServiceIsMissing()
    {
        $this->container->has(RouterInterface::class)->willReturn(true);
        $this->container->has(StreamInterface::class)->willReturn(false);

        $this->expectException(MissingDependencyException::class);
        ($this->factory)($this->container->reveal());
    }

    public function testFactoryProducesImplicitHeadMiddlewareWhenAllDependenciesPresent()
    {
        $router = $this->prophesize(RouterInterface::class);
        $streamFactory = function () {
        };

        $this->container->has(RouterInterface::class)->willReturn(true);
        $this->container->has(StreamInterface::class)->willReturn(true);
        $this->container->get(RouterInterface::class)->will([$router, 'reveal']);
        $this->container->get(StreamInterface::class)->willReturn($streamFactory);

        $middleware = ($this->factory)($this->container->reveal());

        $this->assertInstanceOf(ImplicitHeadMiddleware::class, $middleware);
    }
}