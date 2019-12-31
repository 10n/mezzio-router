# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 3.0.0 - TBD

### Added

- [zendframework/zend-expressive-router#47](https://github.com/zendframework/zend-expressive-router/pull/47) adds
  the middleware `Mezzio\Router\PathBasedRoutingMiddleware`, which
  extends the `RouteMiddleware` to add methods for defining and creating
  path+method based routes. It exposes the following methods:

  - `route(string $path, MiddlewareInterface $middleware, array $methods = null, string $name = null) : Route`
  - `get(string $path, MiddlewareInterface $middleware, string $name = null) : Route`
  - `post(string $path, MiddlewareInterface $middleware, string $name = null) : Route`
  - `put(string $path, MiddlewareInterface $middleware, string $name = null) : Route`
  - `patch(string $path, MiddlewareInterface $middleware, string $name = null) : Route`
  - `delete(string $path, MiddlewareInterface $middleware, string $name = null) : Route`
  - `any(string $path, MiddlewareInterface $middleware, string $name = null) : Route`

- [zendframework/zend-expressive-router#39](https://github.com/zendframework/zend-expressive-router/pull/39) and
  [zendframework/zend-expressive-router#45](https://github.com/zendframework/zend-expressive-router/pull/45) add
  PSR-15 `psr/http-server-middleware` support.

### Changed

- [zendframework/zend-expressive-router#41](https://github.com/zendframework/zend-expressive-router/pull/41) updates
  the `Route` class to provide typehints for all arguments and return values.
  Typehints were generally derived from the existing annotations, with the
  following items of particular note:
  - The constructor `$middleware` argument typehints on the PSR-15
    `MiddlewareInterface`.
  - The `getMiddleware()` method now explicitly returns a PSR-15
    `MiddlewareInterface` instance.
  - `getAllowedMethods()` now returns a nullable `array`.

- [zendframework/zend-expressive-router#41](https://github.com/zendframework/zend-expressive-router/pull/41) and
  [zendframework/zend-expressive-router#43](https://github.com/zendframework/zend-expressive-router/pull/43) update
  the `RouteResult` class to add typehints for all arguments and return values,
  where possible. Typehints were generally derived from the existing
  annotations, with the following items of particular note:
  - The `$methods` argument to `fromRouteFailure()` is now a nullable array
    (with `null` representing the fact that any method is allowed),
    **without a default value**. You must provide a value when creating a route
    failure.
  - `getAllowedMethods()` will now return `['*']` when any HTTP method is
    allowed; this will evaluate to a valid `Allows` header value, and is the
    recommended value when any HTTP method is allowed.

- [zendframework/zend-expressive-router#41](https://github.com/zendframework/zend-expressive-router/pull/41) updates
  the `RouteInterface` to add typehints for all arguments and return values. In
  particular, thse are now:
  - `addRoute(Route $route) : void`
  - `match(Psr\Http\Message\ServerRequestInterface $request) : RouteResult`
  - `generateUri(string $name, array $substitutions = [], array $options = []) : string`

- [zendframework/zend-expressive-router#47](https://github.com/zendframework/zend-expressive-router/pull/47)
  modifies the `RouteMiddleware::$router` property to make it `protected`
  visibility, allowing extensions to work with it.

### Deprecated

- Nothing.

### Removed

- [zendframework/zend-expressive-router#39](https://github.com/zendframework/zend-expressive-router/pull/39) and
  [zendframework/zend-expressive-router#41](https://github.com/zendframework/zend-expressive-router/pull/41) remove
  PHP 5.6 and PHP 7.0 support.

### Fixed

- Nothing.

## 2.3.0 - 2018-02-01

### Added

- [zendframework/zend-expressive-router#46](https://github.com/zendframework/zend-expressive-router/pull/46) adds
  two new middleware, imported from mezzio and re-worked for general
  purpose usage:

  - `Mezzio\Router\RouteMiddleware` composes a router and a response
    prototype. When processed, if no match is found due to an un-matched HTTP
    method, it uses the response prototype to create a 405 response with an
    `Allow` header listing allowed methods; otherwise, it dispatches to the next
    middleware via the provided handler. If a match is made, the route result is
    stored as a request attribute using the `RouteResult` class name, and each
    matched parameter is also added as a request attribute before delegating
    request handling.

  - `Mezzio\Router\DispatchMiddleware` checks for a `RouteResult`
    attribute in the request. If none is found, it delegates handling of the
    request to the handler. If one is found, it pulls the matched middleware and
    processes it. If the middleware is not http-interop middleware, it raises an
    exception.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.2.0 - 2017-10-09

### Added

- [zendframework/zend-expressive-router#36](https://github.com/zendframework/zend-expressive-router/pull/36) adds
  support for http-interop/http-middleware 0.5.0 via a polyfill provided by the
  package webimpress/http-middleware-compatibility. Essentially, this means you
  can drop this package into an application targeting either the 0.4.1 or 0.5.0
  versions of http-middleware, and it will "just work".

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.1.0 - 2017-01-24

### Added

- [zendframework/zend-expressive-router#32](https://github.com/zendframework/zend-expressive-router/pull/32) adds
  support for [http-interop/http-middleware](https://github.com/http-interop/http-middleware)
  server middleware in `Route` instances.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.0.0 - 2017-01-06

### Added

- [zendframework/zend-expressive-router#6](https://github.com/zendframework/zend-expressive-router/pull/6) modifies `RouterInterface::generateUri` to
  support an `$options` parameter, which may pass additional configuration options to the actual router.
- [zendframework/zend-expressive-router#21](https://github.com/zendframework/zend-expressive-router/pull/21) makes the configured path definition
  accessible in the `RouteResult`.

### Deprecated

- Nothing.

### Removed

- Removed `RouteResultObserverInterface` and `RouteResultSubjectInterface`, as they were deprecated in 1.2.0.

### Fixed

- Nothing.

## 1.3.2 - 2016-12-14

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-expressive-router#29](https://github.com/zendframework/zend-expressive-router/pull/29) removes
  the patch introduced with [zendframework/zend-expressive-router#27](https://github.com/zendframework/zend-expressive-router/pull/27)
  and 1.3.1, as it causes `Mezzio\Application` to raise exceptions
  regarding duplicate routes, and because some implementations, including
  FastRoute, also raise errors on duplication. It will be up to individual
  routers to determine how to handle implicit HEAD and OPTIONS support.

## 1.3.1 - 2016-12-13

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-expressive-router#27](https://github.com/zendframework/zend-expressive-router/pull/27) fixes
  the behavior of `Route` to _always_ register `HEAD` and `OPTIONS` as allowed
  methods; this was the original intent of [zendframework/zend-expressive-router#24](https://github.com/zendframework/zend-expressive-router/pull/24).

## 1.3.0 - 2016-12-13

### Added

- [zendframework/zend-expressive-router#23](https://github.com/zendframework/zend-expressive-router/pull/23) adds a
  new static method on the `RouteResult` class, `fromRoute(Route $route, array
  $params = [])`, for creating a new `RouteResult` instance. It also adds
  `getMatchedRoute()` for retrieving the `Route` instance provided to that
  method. Doing so allows retrieving the list of supported HTTP methods, path,
  and route options from the matched route.

- [zendframework/zend-expressive-router#24](https://github.com/zendframework/zend-expressive-router/pull/24) adds
  two new methods to the `Route` class, `implicitHead()` and
  `implicitOptions()`. These can be used by routers or dispatchers to determine
  if a match based on `HEAD` or `OPTIONS` requests was due to the developer
  specifying the methods explicitly when creating the route (the `implicit*()`
  methods will return `false` if explicitly specified).

### Deprecated

- [zendframework/zend-expressive-router#23](https://github.com/zendframework/zend-expressive-router/pull/23)
  deprecates `RouteResult::fromRouteMatch()` in favor of the new `fromRoute()`
  method.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.2.0 - 2016-01-18

### Added

- Nothing.

### Deprecated

- [zendframework/zend-expressive-router#5](https://github.com/zendframework/zend-expressive-router/pull/5)
  deprecates both `RouteResultObserverInterface` and
  `RouteResultSubjectInterface`. The changes introduced in
  [mezzio zendframework/zend-expressive-router#270](https://github.com/zendframework/zend-expressive/pull/270)
  make the system obsolete. The interfaces will be removed in 2.0.0.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.1.0 - 2015-12-06

### Added

- [zendframework/zend-expressive-router#4](https://github.com/zendframework/zend-expressive-router/pull/4) adds
  `RouteResultSubjectInterface`, a complement to `RouteResultObserverInterface`,
  defining the following methods:
  - `attachRouteResultObserver(RouteResultObserverInterface $observer)`
  - `detachRouteResultObserver(RouteResultObserverInterface $observer)`
  - `notifyRouteResultObservers(RouteResult $result)`

### Deprecated

- Nothing.

### Removed

- [zendframework/zend-expressive-router#4](https://github.com/zendframework/zend-expressive-router/pull/4) removes
  the deprecation notice from `RouteResultObserverInterface`.

### Fixed

- Nothing.

## 1.0.1 - 2015-12-03

### Added

- Nothing.

### Deprecated

- [zendframework/zend-expressive-router#3](https://github.com/zendframework/zend-expressive-router/pull/3) deprecates `RouteResultObserverInterface`, which
  [has been moved to the `Mezzio` namespace and package](https://github.com/zendframework/zend-expressive/pull/206).

### Removed

- Nothing.

### Fixed

- [zendframework/zend-expressive-router#1](https://github.com/zendframework/zend-expressive-router/pull/1) fixes the
  coveralls support to trigger after scripts, so the status of the check does
  not make the tests fail. Additionally, ensured that coveralls can receive
  the coverage report!

## 1.0.0 - 2015-12-02

First stable release.

See the [Mezzio CHANGELOG](https://github.com/mezzio/mezzio/blob/master/CHANGELOG.md]
for a history of changes prior to 1.0.
