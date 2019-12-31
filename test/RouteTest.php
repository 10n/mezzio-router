<?php

/**
 * @see       https://github.com/mezzio/mezzio-router for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-router/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-router/blob/master/LICENSE.md New BSD License
 */

namespace MezzioTest\Router;

use Fig\Http\Message\RequestMethodInterface as RequestMethod;
use Mezzio\Router\Exception\InvalidArgumentException;
use Mezzio\Router\Route;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Webimpress\HttpMiddlewareCompatibility\MiddlewareInterface;

/**
 * @covers \Mezzio\Router\Route
 */
class RouteTest extends TestCase
{
    /** @var null|callable */
    private $errorHandler;

    /**
     * @var MiddlewareInterface|ObjectProphecy
     */
    private $noopMiddleware;

    public function setUp()
    {
        $this->noopMiddleware = $this->prophesize(MiddlewareInterface::class)->reveal();
    }

    public function tearDown()
    {
        if ($this->errorHandler) {
            restore_error_handler();
            $this->errorHandler = null;
        }
    }

    public function registerDeprecationErrorHandler()
    {
        $this->errorHandler = set_error_handler(function ($errno, $errstr) {
            return true;
        }, E_USER_DEPRECATED);
    }

    public function testRoutePathIsRetrievable()
    {
        $route = new Route('/foo', $this->noopMiddleware);
        $this->assertEquals('/foo', $route->getPath());
    }

    public function testRouteMiddlewareIsRetrievable()
    {
        $route = new Route('/foo', $this->noopMiddleware);
        $this->assertSame($this->noopMiddleware, $route->getMiddleware());
    }

    public function testRouteMiddlewareMayBeANonCallableString()
    {
        $this->registerDeprecationErrorHandler();
        $route = new Route('/foo', 'Application\Middleware\HelloWorld');
        $this->assertSame('Application\Middleware\HelloWorld', $route->getMiddleware());
    }

    public function testRouteInstanceAcceptsAllHttpMethodsByDefault()
    {
        $route = new Route('/foo', $this->noopMiddleware);
        $this->assertSame(Route::HTTP_METHOD_ANY, $route->getAllowedMethods());
    }

    public function testRouteAllowsSpecifyingHttpMethods()
    {
        $methods = [RequestMethod::METHOD_GET, RequestMethod::METHOD_POST];
        $route = new Route('/foo', $this->noopMiddleware, $methods);
        $this->assertSame($methods, $route->getAllowedMethods());
    }

    public function testRouteCanMatchMethod()
    {
        $methods = [RequestMethod::METHOD_GET, RequestMethod::METHOD_POST];
        $route = new Route('/foo', $this->noopMiddleware, $methods);
        $this->assertTrue($route->allowsMethod(RequestMethod::METHOD_GET));
        $this->assertTrue($route->allowsMethod(RequestMethod::METHOD_POST));
        $this->assertFalse($route->allowsMethod(RequestMethod::METHOD_PATCH));
        $this->assertFalse($route->allowsMethod(RequestMethod::METHOD_DELETE));
    }

    public function testRouteAlwaysAllowsHeadMethod()
    {
        $route = new Route('/foo', $this->noopMiddleware, []);
        $this->assertTrue($route->allowsMethod(RequestMethod::METHOD_HEAD));
    }

    public function testRouteAlwaysAllowsOptionsMethod()
    {
        $route = new Route('/foo', $this->noopMiddleware, []);
        $this->assertTrue($route->allowsMethod(RequestMethod::METHOD_OPTIONS));
    }

    public function testRouteAllowsSpecifyingOptions()
    {
        $options = ['foo' => 'bar'];
        $route = new Route('/foo', $this->noopMiddleware);
        $route->setOptions($options);
        $this->assertSame($options, $route->getOptions());
    }

    public function testRouteOptionsAreEmptyByDefault()
    {
        $route = new Route('/foo', $this->noopMiddleware);
        $this->assertSame([], $route->getOptions());
    }

    public function testRouteNameForRouteAcceptingAnyMethodMatchesPathByDefault()
    {
        $route = new Route('/test', $this->noopMiddleware);
        $this->assertSame('/test', $route->getName());
    }

    public function testRouteNameWithConstructor()
    {
        $route = new Route('/test', $this->noopMiddleware, [], 'test');
        $this->assertSame('test', $route->getName());
    }

    public function testRouteNameWithGET()
    {
        $route = new Route('/test', $this->noopMiddleware, [ RequestMethod::METHOD_GET ]);
        $this->assertSame('/test^GET', $route->getName());
    }

    public function testRouteNameWithGetAndPost()
    {
        $route = new Route('/test', $this->noopMiddleware, [ RequestMethod::METHOD_GET, RequestMethod::METHOD_POST ]);
        $this->assertSame('/test^GET' . Route::HTTP_METHOD_SEPARATOR . 'POST', $route->getName());
    }

    public function testThrowsExceptionDuringConstructionIfPathIsNotString()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid path; must be a string');

        new Route(12345, $this->noopMiddleware);
    }

    public function testThrowsExceptionDuringConstructionOnInvalidMiddleware()
    {
        $this->registerDeprecationErrorHandler();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid middleware');

        new Route('/foo', 12345);
    }

    public function testThrowsExceptionDuringConstructionOnInvalidHttpMethod()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'Invalid HTTP methods; must be an array or %s::HTTP_METHOD_ANY',
            Route::class
        ));

        new Route('/foo', $this->noopMiddleware, 'FOO');
    }

    public function testRouteNameIsMutable()
    {
        $route = new Route('/foo', $this->noopMiddleware, [RequestMethod::METHOD_GET], 'foo');
        $route->setName('bar');

        $this->assertSame('bar', $route->getName());
    }

    public function invalidHttpMethodsProvider()
    {
        return [
            [[123]],
            [[123, 456]],
            [['@@@']],
            [['@@@', '@@@']],
        ];
    }

    /**
     * @dataProvider invalidHttpMethodsProvider
     *
     * @param array $invalidHttpMethods
     */
    public function testThrowsExceptionIfInvalidHttpMethodsAreProvided(array $invalidHttpMethods)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('One or more HTTP methods were invalid');

        new Route('/test', $this->noopMiddleware, $invalidHttpMethods);
    }

    public function testProvidingArrayOfMethodsWithoutHeadOrOptionsImpliesBoth()
    {
        $route = new Route('/test', $this->noopMiddleware, [RequestMethod::METHOD_GET, RequestMethod::METHOD_POST]);
        $this->assertTrue($route->implicitHead());
        $this->assertTrue($route->implicitOptions());
    }

    public function headAndOptions()
    {
        return [
            'head'    => [RequestMethod::METHOD_HEAD, 'implicitHead'],
            'options' => [RequestMethod::METHOD_OPTIONS, 'implicitOptions'],
        ];
    }

    /**
     * @dataProvider headAndOptions
     *
     * @param string $httpMethod
     * @param string $implicitMethod
     */
    public function testPassingHeadOrOptionsInMethodArrayDoesNotMarkAsImplicit($httpMethod, $implicitMethod)
    {
        $route = new Route('/test', $this->noopMiddleware, [$httpMethod]);
        $this->assertFalse($route->{$implicitMethod}());
    }

    public function testPassingWildcardMethodDoesNotMarkAsImplicit()
    {
        $route = new Route('/test', $this->noopMiddleware, Route::HTTP_METHOD_ANY);
        $this->assertFalse($route->implicitHead());
        $this->assertFalse($route->implicitOptions());
    }

    public function testAllowsHttpInteropMiddleware()
    {
        $middleware = $this->prophesize(MiddlewareInterface::class)->reveal();
        $route = new Route('/test', $middleware, Route::HTTP_METHOD_ANY);
        $this->assertSame($middleware, $route->getMiddleware());
    }

    /**
     * This is to allow passing an array of middleware for use in creating a MiddlewarePipe
     * instance; Route should simply check if it's a non-callable array, and, if so, store
     * the entry as it would a non-callable string.
     */
    public function testAllowsNonCallableArraysAsMiddleware()
    {
        $this->registerDeprecationErrorHandler();
        $middleware = ['Non', 'Callable', 'Middleware'];
        $route = new Route('/test', $middleware, Route::HTTP_METHOD_ANY);
        $this->assertSame($middleware, $route->getMiddleware());
    }

    public function invalidMiddleware()
    {
        // Strings are allowed, because they could be service names.
        return [
            'null'                => [null],
            'true'                => [true],
            'false'               => [false],
            'zero'                => [0],
            'int'                 => [1],
            'non-callable-object' => [(object) ['handler' => 'foo']],
        ];
    }

    /**
     * @dataProvider invalidMiddleware
     *
     * @param mixed $middleware
     */
    public function testConstructorRaisesExceptionForInvalidMiddleware($middleware)
    {
        $this->registerDeprecationErrorHandler();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid middleware');

        new Route('/test', $middleware);
    }

    public function validNonInteropMiddleware()
    {
        yield 'string-callable' => ['sprintf'];
        yield 'array' => [['sprintf']];
        yield 'array-callable' => [[$this, 'setUp']];
    }

    /**
     * @dataProvider validNonInteropMiddleware
     * @param mixed $middleware
     */
    public function testPassingNonInteropMiddlewareToConstructorTriggersDeprecationNotice($middleware)
    {
        $spy = (object) ['found' => false];
        $this->errorHandler = set_error_handler(function ($errno, $errstr) use ($spy) {
            $spy->found = true;
            return true;
        }, E_USER_DEPRECATED);

        new Route('/test', $middleware);

        $this->assertTrue($spy->found);
    }
}
