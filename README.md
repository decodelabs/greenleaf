# Greenleaf

[![PHP from Packagist](https://img.shields.io/packagist/php-v/decodelabs/greenleaf?style=flat)](https://packagist.org/packages/decodelabs/greenleaf)
[![Latest Version](https://img.shields.io/packagist/v/decodelabs/greenleaf.svg?style=flat)](https://packagist.org/packages/decodelabs/greenleaf)
[![Total Downloads](https://img.shields.io/packagist/dt/decodelabs/greenleaf.svg?style=flat)](https://packagist.org/packages/decodelabs/greenleaf)
[![GitHub Workflow Status](https://img.shields.io/github/actions/workflow/status/decodelabs/greenleaf/integrate.yml?branch=develop)](https://github.com/decodelabs/greenleaf/actions/workflows/integrate.yml)
[![PHPStan](https://img.shields.io/badge/PHPStan-enabled-44CC11.svg?longCache=true&style=flat)](https://github.com/phpstan/phpstan)
[![License](https://img.shields.io/packagist/l/decodelabs/greenleaf?style=flat)](https://packagist.org/packages/decodelabs/greenleaf)

### Super-fast directory based HTTP router

Greenleaf provides a fast and flexible way to route HTTP requests to your actions and pages.

---

## Installation

Install via Composer:

```bash
composer require decodelabs/greenleaf
```

## Usage

The main entry point to use Greenleaf is a PSR-15 middleware that can be used with any PSR-15 compatible framework, such as [Harvest](https://github.com/decodelabs/harvest). It will parse the request and attempt to match it against a set of configured routes.

The system is comprised of a set of generators that can scan and find available routes, a collection of flexible route types and an extensible dispatcher architecture that allows for different matching strategies.

### Dispatcher

While the dispatcher is usually accessed through middleware, you can if needed instantiate the Dispatcher directly and treat it as a standard PSR HTTP Handler.

```php
use DecodeLabs\Greenleaf;
use DecodeLabs\Harvest;

$dispatcher = Greenleaf::createDispatcher();
$request = Harvest::createRequestFromEnvironment();
$response = $dispatcher->handle($request);
```

### Namespace

The router will use [Archetype](https://github.com/decodelabs/archetype) to resolve Action classes from the URL - you can use Archetype's namespace mapping functionality to mount a directory for Http related classes:

```php
use DecodeLabs\Archetype;
use MyApp\Http;

Archetype::alias('DecodeLabs\\Greenleaf\\*', Http::class);
```

If your app is based on the [Fabric](https://github.com/decodelabs/fabric) framework, this mapping is taken care of for you automatically, based on the app namespace in your config.

### Generators

Generators are used to load and configure routes. The `Generator` interface defines a simple mechanism for implementations to find and load routes.

By default, Greenleaf will load a `Scanner` Generator which in turn will scan the directory tree for other Generators and load the Actions they provide.

To define Routes in your directory tree, you can start with a generic `Routes` Generator which expects routes to be defined by hand:


```php
namespace MyApp\Http;

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

When the Dispatcher runs, it loads an appropriate `Router` to take care of matching the request to the configured routes.

As of the current release, Greenleaf uses a generated switch based `PatternSwitch` Router that is extremely fast and efficient. There is also a `CheckEach` Router that runs a brute force loop over each route in turn - this is a very stable and predictable model, but is not recommended for production use.

When a Router implementation finds a match, it transforms the route pattern into a custom "leaf URL" in the format `leaf:/path/to/file?query#fragment` and a set of parameters that are then passed to the target of the route.

This is one of Greenleaf's biggest strengths as both input and output forms resolve to URL formats that can be easily looked up and matched against, both when matching _in_ and when generating _out_.

### Leaf URL

The URL format is mostly just a subset of HTTP URLs, with `leaf` as the scheme, standard path, query and fragment components.

For example:

```php
// Route
Greenleaf::action('test/{slug}', 'test?hello');

// Creates URI
Greenleaf::uri('leaf:/test?hello');
// $parameters = ['slug' => 'value-of-slug-in-request']

// Resolves to:
$actionClass = MyApp\Http\Test::class;

// --------------------------
// Or
Greenleaf::action('blog/articles', 'blog/articles');

// Creates URI
Greenleaf::uri('leaf:/blog/articles');

// Resolves to:
$actionClass = MyApp\Http\Blog\Articles::class;
```

### Routes

There are currently three types of route available in Greenleaf:

- **Action**: A route that maps to an Action class. The action must implement the `DecodeLabs\Greenleaf\Action` interface and provides the most flexibility in responding to requests
- **Page**: A route that maps to page components, in the same vein as the likes of Next.js. `PageAction` adapters handle loading different types of page content
- **Redirect**: A route that redirects the request to a different URL. This is useful for handling legacy URLs or for redirecting to a different domain


#### Action

Actions need to implement an `execute(DecodeLabs\Greenleaf\Request $request)` method, and return a Response. Greenleaf provides a number of traits that can be used to add additional functionality and simplify writing logic for your views.

The `ByMethodTrait` for example will attempt to invoke a method on the Action based on the HTTP method of the request.

Note that most traits that work in this fashion will use `Slingshot` to invoke the method with deep dependency injection support. In this example, the slug from the matched route request URL is passed as a string to the action handlers.

```php
namespace MyApp\Http;

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

Actions don't necessarily need to return PSR-7 responses directly - Greenleaf uses [Harvest's](https://github.com/decodelabs/harvest) response transformation system to convert all types of content responses to full PSR-7 HTTP responses.


#### Page

A page route is usually resolved to a content file or other type of component. Greenleaf provides a basic HTML file adapter that will load a file from the filesystem and return it as a response, however other packages provide more complex implementations, such as [Horizon](https://github.com/decodelabs/horizon) `Page` and `Fragment` component structures.

When a page route is matched, the file path is resolved using [Monarch's](https://github.com/decodelabs/monarch) path alias system, from a base path of `@pages`. This alias if not defined by your app, defaults to the `@run/src/components/pages` directory.

```php
...

// HTML file /src/components/pages/about.html
yield Greenleaf::page('about', 'about.html');

// Horizon Page /src/components/pages/blog.php
yield Greenleaf::page('blog', 'blog.php');

// Set default component type
Greenleaf::setDefaultPageType('php');

// Same as above
yield Greenleaf::page('blog');
```

### HTTP URLs

One of the main benefits of Greenleaf is that it allows you to generate URLs for your routes in a simple and flexible way by creating leaf URLs with many of the required URL constructs already in place.

The router will then be able to match these URLs to the correct route and pass the parameters to the HTTP URL generator.

```php
use DecodeLabs\Greenleaf;

// route pattern: test/{slug}

$url = Greenleaf::url(
    'test?hello#fragment',
    ['slug' => 'my-slug']
);

// https://mydomain.localtest.me/test/my-slug?hello#fragment
```

## Licensing

Greenleaf is licensed under the MIT License. See [LICENSE](./LICENSE) for the full license text.
