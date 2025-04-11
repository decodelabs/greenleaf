# Greenleaf

[![PHP from Packagist](https://img.shields.io/packagist/php-v/decodelabs/greenleaf?style=flat)](https://packagist.org/packages/decodelabs/greenleaf)
[![Latest Version](https://img.shields.io/packagist/v/decodelabs/greenleaf.svg?style=flat)](https://packagist.org/packages/decodelabs/greenleaf)
[![Total Downloads](https://img.shields.io/packagist/dt/decodelabs/greenleaf.svg?style=flat)](https://packagist.org/packages/decodelabs/greenleaf)
[![GitHub Workflow Status](https://img.shields.io/github/actions/workflow/status/decodelabs/greenleaf/integrate.yml?branch=develop)](https://github.com/decodelabs/greenleaf/actions/workflows/integrate.yml)
[![PHPStan](https://img.shields.io/badge/PHPStan-enabled-44CC11.svg?longCache=true&style=flat)](https://github.com/phpstan/phpstan)
[![License](https://img.shields.io/packagist/l/decodelabs/greenleaf?style=flat)](https://packagist.org/packages/decodelabs/greenleaf)

### Super-fast directory based HTTP router

Greenleaf provides a simple, fast and flexible way to route HTTP requests to controllers and actions based on the URL path.

_Get news and updates on the [DecodeLabs blog](https://blog.decodelabs.com)._

---

## Installation

Install via Composer:

```bash
composer require decodelabs/greenleaf
```

## Usage

Greenleaf provides a PSR-15 middleware that can be used with any PSR-15 compatible framework. It will parse the request and attempt to match it against a set of configured routes.

The heart of Greenleaf is a directory based class mapping that enables loading `Generators`, `Routes` and `Actions` from a directory tree that closer matches the structure of most web apps from a logical perspective.

### Dispatcher

Greenleaf exposes its Dispatcher via a Harvest Middleware to allow for easy integration and automated class resolution with Harvest.

You can however instantiate the Dispatcher directly and treat it as a standard PSR HTTP Handler.

```php
use DecodeLabs\Greenleaf;
use DecodeLabs\Harvest;

$dispatcher = Greenleaf::createDispatcher();

$request = Harvest::createRequestFromEnvironment();
$response = $dispatcher->handle($request);
```

### Generators

Generators are used to load and configure routes. They are simple classes that implement the `Generator` interface.

By default, Greenleaf will load a `Scanner` Generator which in turn will scan the configured directory tree for other Generators and load the Actions they provide.

To define Routes in your directory tree, you can start with a Generic Routes Generator.


```php

namespace MyApp\Greenleaf;

use DecodeLabs\Greenleaf;
use DecodeLabs\Greenleaf\Generator;
use DecodeLabs\Greenleaf\GeneratorTrait;

class Routes implements Generator
{
    use GeneratorTrait;

    public function generateRoutes(): iterable
    {
        // Basic route
        yield Greenleaf::action('/', 'home');

        // Basic route with parameter
        yield Greenleaf::action('test/{slug}', 'test')

        // Route with inset parameters
        yield Greenleaf::action('test-{slug}/', 'test?hello')
            ->with('slug', validate: 'slug');

        // Route with multi-part path parameters
        yield Greenleaf::action('assets/{path}', 'assets')
            ->with('path', validate: 'path');

        // Redirect
        yield Greenleaf::redirect('old/path', 'new/path');
    }
}
```

### Router

When the Dispatcher runs, it loads an appropriate Router to take care of matching the Request to the configured Routes.

At this early stage, Greenleaf provides a reference Matching implementation that just brute forces its way through the list of Routes until it finds a match. This implementation is not optimised for speed and will be replaced shortly with a high performance compiled router system that will be able to handle thousands of routes with ease.

When a Router implementation finds a match, it transforms the Route pattern into a Greenleaf custom URI and a set of parameters that are then passed to the Action (if relevant).

### Greenleaf URI

The URI format is mostly just a subset of HTTP URLs, with `leaf` as the scheme, standard path, query and fragment components.

For example:

```php
// Route
Greenleaf::action('test/{slug}', 'test?hello');

// Creates URI
Greenleaf::uri('leaf:/test?hello');
// $parameters = ['slug' => 'value-of-slug-in-request']

// Resolves to:
$actionClass = MyApp\Greenleaf\TestAction::class;

// --------------------------
// Or
Greenleaf::action('admin/blog/articles', 'admin/blog/articles');

// Creates URI
Greenleaf::uri('leaf:/admin/blog/articles');

// Resolves to:
$actionClass = MyApp\Greenleaf\Blog\ArticlesAction::class;
```

### Actions

Once loaded, an Action only needs to implement an `execute(DecodeLabs\Greenleaf\Request $request)` method, however Greenleaf provides a number of traits that can be used to add additional functionality.

The `ByMethodTrait` for example will attempt to invoke a method on the Action based on the HTTP method of the request.

Note that most traits that work in this fashion will use `Slingshot` to invoke the method with deep dependency injection support. In this example, the slug from the matched route request URL is passed as a string to the action handlers.

```php
namespace MyApp\Greenleaf;

use DecodeLabs\Harvest;
use DecodeLabs\Harvest\Response;
use DecodeLabs\Greenleaf;
use DecodeLabs\Greenleaf\Action;
use DecodeLabs\Greenleaf\Action\ByMethodTrait;

class Test implements Action
{
    use ByMethodTrait;

    public function get(
        string $slug
    ): Response {
        return Harvest::text('Get response');
    }

    public function post(
        string $slug
    ): Response {
        return Harvest::text('Post response');
    }
}
```

### URLs

One of the main benefits of Greenleaf is that it allows you to generate URLs for your routes in a simple and flexible way by creating leaf URIs with many of the required URL constructs already in place.

The router will then be able to match these URIs to the correct route and pass the parameters to the URL generator.

```php
use DecodeLabs\Greenleaf;

// route pattern: test/{slug}

$url = Greenleaf::createUrl(
    'test?hello#fragment',
    ['slug' => 'my-slug']
);

// https://mydomain.localtest.me/test/my-slug?hello#fragment
```

## Licensing

Greenleaf is licensed under the MIT License. See [LICENSE](./LICENSE) for the full license text.
