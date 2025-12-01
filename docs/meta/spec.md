# Greenleaf — Package Specification

> **Cluster:** `http`
> **Language:** `php`
> **Milestone:** `m3`
> **Repo:** `https://github.com/decodelabs/greenleaf`
> **Role:** HTTP router

This document describes the purpose, contracts, and design of **Greenleaf** within the Decode Labs ecosystem.

It is aimed at:

- Developers **using** Greenleaf for HTTP routing.
- Contributors **maintaining or extending** Greenleaf.
- Tools and AI assistants that need to reason about its behaviour.

---

## 1. Overview

### 1.1 Purpose

Greenleaf provides a super-fast directory-based HTTP router for PHP applications. It offers a fast and flexible way to route HTTP requests to actions and pages, with support for route generation, pattern matching, parameter extraction, and URL generation. Greenleaf uses a generated switch-based router (`PatternSwitch`) that is extremely fast and efficient, as well as a brute-force router (`CheckEach`) for simpler use cases. The system supports multiple route types (Action, Page, Redirect), flexible parameter types (Slug, Path, Number, Decimal, Pattern, Options), and bidirectional routing (matching incoming requests and generating outgoing URLs). Greenleaf integrates with Archetype for class resolution, Harvest for HTTP handling, and Singularity for URL management.

### 1.2 Non-Goals

Greenleaf does **not**:

- Provide application-specific business logic or domain models
- Implement middleware or request/response transformation (uses Harvest)
- Provide database abstraction or ORM functionality
- Implement user authentication or authorization
- Provide templating or view rendering (beyond page file loading)
- Implement session management or caching
- Provide asset management or bundling
- Implement form handling or validation
- Provide application scaffolding or code generation
- Implement deployment or DevOps tooling
- Provide testing frameworks or test runners
- Implement logging backends
- Provide performance profiling or monitoring
- Implement APM or distributed tracing

Greenleaf focuses on HTTP routing, not on implementing application features or infrastructure beyond routing.

---

## 2. Role in the Ecosystem

### 2.1 Cluster & Positioning

- **Cluster:** `http` (see Chorus taxonomy)
- Greenleaf is positioned as an HTTP routing system that provides fast and flexible request routing for PHP applications. It sits at a high level in the dependency graph, depending on several core packages (Archetype, Atlas, Coercion, Dictum, Exceptional, Harvest, Iota, Kingdom, Monarch, Nuance, Singularity, Slingshot) and integrating with PSR-15 middleware and PSR-7 HTTP messages. Greenleaf is used by frameworks like Fabric and applications built on the Decode Labs ecosystem to provide routing capabilities. It integrates with Archetype for class resolution, Harvest for HTTP handling, Singularity for URL management, and Slingshot for dependency injection.

### 2.2 Typical Usage Contexts

Typical places Greenleaf appears:

- HTTP request routing (via PSR-15 middleware)
- Action class resolution and dispatching
- Page component routing
- URL generation for routes
- Route pattern matching
- Parameter extraction and validation
- Redirect handling
- Route generation from directory structure
- Framework routing integration (e.g., Fabric)
- Application routing configuration

Greenleaf is intended to be used whenever an application needs to:
- Route HTTP requests to action classes
- Generate URLs for routes
- Match request paths to route patterns
- Extract and validate route parameters
- Handle redirects
- Generate routes from directory structure
- Provide fast routing performance

---

## 3. Public Surface

> This section focuses on the conceptual API, not every symbol.

### 3.1 Key Types

The primary public types are:

- `DecodeLabs\Greenleaf`
  Main service that orchestrates routing, URL generation, and dispatcher creation. Implements `Service`. Provides methods for matching incoming requests, matching outgoing URLs, generating HTTP URLs, creating dispatchers, and managing cache.

- `DecodeLabs\Greenleaf\Dispatcher`
  Interface for PSR-15 request handlers that dispatch requests to routes. Extends `Psr\Http\Server\RequestHandlerInterface`.

- `DecodeLabs\Greenleaf\Middleware`
  PSR-15 middleware implementation that integrates Greenleaf with Harvest. Implements `Dispatcher` and `Harvest\Middleware`.

- `DecodeLabs\Greenleaf\Router`
  Interface for route matching strategies. Defines methods for matching incoming requests and outgoing URLs.

- `DecodeLabs\Greenleaf\Router\PatternSwitch`
  Fast switch-based router implementation. Generates optimized PHP code for route matching. Implements `Router` and `Caching`.

- `DecodeLabs\Greenleaf\Router\CheckEach`
  Simple brute-force router implementation. Checks each route in sequence. Implements `Router` and `Caching`.

- `DecodeLabs\Greenleaf\Router\Caching`
  Interface for routers that support caching. Defines methods for clearing and rebuilding cache.

- `DecodeLabs\Greenleaf\Generator`
  Interface for route generators. Defines method for generating routes.

- `DecodeLabs\Greenleaf\Generator\Collector`
  Default generator that collects routes from other generators. Implements `Generator`, `Caching`, and `Orderable`.

- `DecodeLabs\Greenleaf\Generator\Directory`
  Generator that scans directory structure for Action classes and userland generators. Implements `Generator` and `Orderable`.

- `DecodeLabs\Greenleaf\Generator\Pages`
  Generator that scans for PageAction implementations. Implements `Generator` and `Orderable`.

- `DecodeLabs\Greenleaf\Generator\Caching`
  Interface for generators that support caching. Defines methods for clearing and rebuilding cache.

- `DecodeLabs\Greenleaf\Generator\Orderable`
  Interface for generators that can be ordered by priority. Defines priority property.

- `DecodeLabs\Greenleaf\Route`
  Interface for route definitions. Defines pattern, parameters, methods, matching, and handling. Extends `JsonSerializable`.

- `DecodeLabs\Greenleaf\Route\Action`
  Route type that maps to Action classes. Implements `Route` and `Bidirectional`. Uses Archetype to resolve Action classes.

- `DecodeLabs\Greenleaf\Route\Page`
  Route type that maps to page components. Implements `Route` and `Bidirectional`. Uses Archetype to resolve PageAction classes.

- `DecodeLabs\Greenleaf\Route\Redirect`
  Route type that redirects to different URLs. Implements `Route`. Supports permanent/temporary redirects and query parameter mapping.

- `DecodeLabs\Greenleaf\Route\Pattern`
  Represents a route pattern with segments. Implements `Stringable` and `Dumpable`. Parses patterns into segments.

- `DecodeLabs\Greenleaf\Route\Segment`
  Represents a single segment of a route pattern. Implements `Stringable` and `Dumpable`. Contains tokens (strings and parameters).

- `DecodeLabs\Greenleaf\Route\Parameter`
  Base class for route parameters. Implements `JsonSerializable`. Defines validation and resolution logic.

- `DecodeLabs\Greenleaf\Route\Parameter\Slug`
  Parameter type for URL-friendly slugs. Validates and resolves using Dictum slug formatting.

- `DecodeLabs\Greenleaf\Route\Parameter\Path`
  Parameter type for multi-segment paths. Supports capturing multiple path segments.

- `DecodeLabs\Greenleaf\Route\Parameter\Number`
  Parameter type for numeric values. Validates digits and resolves to integers.

- `DecodeLabs\Greenleaf\Route\Parameter\Decimal`
  Parameter type for decimal values. Validates numeric format.

- `DecodeLabs\Greenleaf\Route\Parameter\Pattern`
  Parameter type with custom regex pattern. Allows custom validation rules.

- `DecodeLabs\Greenleaf\Route\Parameter\Options`
  Parameter type with allowed values. Validates against predefined options.

- `DecodeLabs\Greenleaf\Route\Hit`
  Result of route matching. Contains matched route, extracted parameters, and query string.

- `DecodeLabs\Greenleaf\Route\Bidirectional`
  Interface for routes that support both incoming and outgoing matching. Defines target LeafUrl property.

- `DecodeLabs\Greenleaf\Action`
  Interface for action classes. Defines methods for middleware, supported methods, and execution.

- `DecodeLabs\Greenleaf\Action\ByMethodTrait`
  Trait for actions that dispatch by HTTP method. Automatically invokes methods based on request method.

- `DecodeLabs\Greenleaf\Action\JsonApiTrait`
  Trait for JSON API actions. Sets default content type to application/json.

- `DecodeLabs\Greenleaf\PageAction`
  Interface for page action handlers. Extends `Action`, `Generator`, and `Orderable`.

- `DecodeLabs\Greenleaf\PageAction\Html`
  Page action handler for HTML files. Loads files from `@pages` path alias.

- `DecodeLabs\Greenleaf\Request`
  Wrapper for HTTP requests with Leaf URL and route parameters. Provides access to HTTP request, Leaf URL, parameters, and route.

- `DecodeLabs\Greenleaf\Middleware`
  Attribute class for defining middleware on actions. Supports PSR-15 middleware, class names, and closures.

- `DecodeLabs\Singularity\Url\Leaf`
  Leaf URL implementation for internal routing. Extends Singularity URL with leaf scheme. Used for route targets and matching.

### 3.2 Main Entry Points

The main usage pattern is through PSR-15 middleware:

```php
use DecodeLabs\Greenleaf;
use DecodeLabs\Monarch;

$greenleaf = Monarch::getService(Greenleaf::class);
$dispatcher = $greenleaf->createDispatcher();
$response = $dispatcher->handle($request);
```

Or via Harvest middleware:

```php
use DecodeLabs\Harvest\Middleware\Greenleaf;

$middleware = new Greenleaf($greenleaf);
$response = $middleware->process($request, $next);
```

Route generation:

```php
namespace MyApp\Http;

use DecodeLabs\Greenleaf\Generator;
use DecodeLabs\Greenleaf\Route\Action;
use DecodeLabs\Greenleaf\Route\Parameter;

class Routes implements Generator
{
    public function generateRoutes(): iterable
    {
        yield new Action('/', 'home');
        yield new Action('test/{slug}', 'test', parameters: [
            new Parameter\Slug('slug')
        ]);
    }
}
```

URL generation:

```php
$url = $greenleaf->url('leaf:/test?hello', ['slug' => 'my-slug']);
// https://mydomain.com/test/my-slug?hello
```

---

## 4. Dependencies

### 4.1 Decode Labs

- `decodelabs/archetype` (required)
  Used for resolving Action and Generator classes from names. Greenleaf registers Archetype resolvers for Generator and Action interfaces.

- `decodelabs/atlas` (required)
  Used for file system operations in page action handlers and directory scanning.

- `decodelabs/coercion` (required)
  Used for type coercion in parameter resolution and value conversion.

- `decodelabs/dictum` (required)
  Used for slug formatting in Slug parameter type.

- `decodelabs/exceptional` (required)
  Used for exception handling and creating route-related exceptions.

- `decodelabs/harvest` (required)
  Used for HTTP request/response handling, middleware integration, and response transformation.

- `decodelabs/iota` (required)
  Used for caching route data and generated router code.

- `decodelabs/kingdom` (required)
  Used for service container integration (Greenleaf implements `Service`).

- `decodelabs/monarch` (required)
  Used for global service location, path alias management (`@pages`), and environment detection.

- `decodelabs/nuance` (required)
  Used for data inspection and entity rendering (Dumpable interface).

- `decodelabs/singularity` (required)
  Used for URL management, including Leaf URL implementation and HTTP URL generation.

- `decodelabs/slingshot` (required)
  Used for dependency injection when instantiating actions and generators.

### 4.2 External

- `psr/container` (required, ^2.0.2)
  Used for PSR-11 container interface.

- `psr/http-message` (required, ^2.0)
  Used for PSR-7 HTTP message interfaces.

- `psr/http-server-handler` (required, ^1.0.2)
  Used for PSR-15 request handler interface.

- `psr/http-server-middleware` (required, ^1.0.2)
  Used for PSR-15 middleware interface.

### 4.3 Optional Integrations

- `decodelabs/guidance` (suggested)
  Detected at runtime if installed, used for UID route support.

---

## 5. Behaviour & Contracts

### 5.1 Invariants

- Greenleaf service registers Archetype resolvers for Generator and Action on construction
- Greenleaf service sets up `@pages` path alias if not already defined
- Router is lazy-loaded and cached via Iota
- Generator collects routes from registered generators in priority order
- Routes are sorted by pattern specificity (more specific first)
- PatternSwitch router generates optimized PHP code for matching
- PatternSwitch router caches generated code in Iota
- Routes support HTTP method filtering
- Routes support parameter validation and resolution
- Routes support default parameter values
- Routes support multi-segment parameters (must be last segment)
- Action routes resolve Action classes via Archetype
- Page routes resolve PageAction classes via Archetype
- Redirect routes support permanent/temporary redirects
- Redirect routes support query parameter mapping
- Leaf URLs are used for internal route representation
- Leaf URLs are converted to HTTP URLs for output
- URL generation matches Leaf URLs to routes
- URL generation resolves parameters into path segments
- URL generation preserves query strings and fragments
- Directory generator scans for Action classes and userland generators
- Directory generator generates routes from Action class attributes
- Pages generator scans for PageAction implementations
- Collector generator collects routes from Directory and Pages generators
- Route matching validates HTTP methods
- Route matching validates parameters
- Route matching extracts parameters from path segments
- Route matching supports default parameter values
- Route matching supports multi-segment parameters
- Outgoing matching matches Leaf URLs to route targets
- Outgoing matching merges query parameters
- Action execution uses Slingshot for dependency injection
- Action execution transforms responses via Harvest
- Action execution supports middleware via Middleware attribute
- ByMethodTrait dispatches to methods based on HTTP method
- ByMethodTrait supports method names with parameter suffixes
- ByMethodTrait handles unknown methods with Allow header
- PageActionTrait scans page files from `@pages` path
- PageActionTrait converts file names to route patterns
- Html page action loads files from filesystem
- Middleware attribute supports PSR-15 middleware, class names, and closures
- Request wrapper provides access to HTTP request, Leaf URL, parameters, and route
- Route parameters support validation and resolution
- Route parameters support default values
- Route segments support dynamic tokens (parameters)
- Route segments support static tokens (strings)
- Route segments support multi-segment parameters
- Route patterns are normalized to start with `/`
- Route patterns are parsed into segments
- Route patterns support parameter placeholders `{name}`
- Route patterns support inset parameters `test-{slug}/`
- Route patterns support multi-part path parameters `assets/{path}`
- Slug parameter validates URL-friendly format
- Slug parameter resolves using Dictum slug formatting
- Path parameter captures multiple segments
- Number parameter validates digits
- Number parameter resolves to integers
- Decimal parameter validates numeric format
- Pattern parameter uses custom regex
- Options parameter validates against allowed values
- Hit contains matched route, parameters, and query string
- Dispatcher handles PSR-15 requests
- Middleware integrates with Harvest middleware system
- Middleware supports development cache clearing
- Middleware supports directory matching (trailing slash handling)
- Router caching clears Iota repository
- Router caching rebuilds generated code
- Generator caching clears Iota repository
- Generator caching rebuilds route data
- Route serialization supports JSON export
- Route deserialization supports JSON import
- Leaf URL parsing supports path, query, and fragment
- Leaf URL to class name conversion uses Dictum ID formatting
- Leaf URL to class name conversion handles index routes
- HTTP URL generation uses Singularity HttpUrl
- HTTP URL generation preserves query and fragment
- HTTP URL generation resolves parameters into path
- Route matching handles trailing slash differences
- Route matching redirects trailing slash mismatches
- Route matching supports OPTIONS and HEAD methods automatically
- Route matching returns null if no match found
- URL generation throws exception if no match found
- Action resolution uses Archetype namespace mapping
- Action resolution supports class name derivation from Leaf URL
- Page action resolution uses file extension to determine handler
- Page action resolution defaults to HTML if no extension
- Middleware execution uses Harvest middleware dispatcher
- Response transformation uses Harvest transformer
- Exception handling in actions supports JSON responses
- Exception handling in actions supports HTML responses
- Development mode cache clearing is automatic
- Development mode cache rebuilding is automatic
- Production mode caching is persistent
- Route generation is lazy (only when needed)
- Router code generation is lazy (only when needed)
- Route data caching is lazy (only when needed)

### 5.2 Input & Output Contracts

**Greenleaf Methods:**
- `createDispatcher(): Dispatcher` — Creates PSR-15 dispatcher. Returns Dispatcher instance.
- `matchIn(PsrRequest $request, bool $checkDir = false): ?Hit` — Matches incoming request. Returns Hit or null.
- `matchOut(string|LeafUrl $uri, ?array $parameters = null): Hit` — Matches outgoing URL. Returns Hit or throws exception.
- `url(string|LeafUrl $uri, string|Stringable|int|float|bool|null ...$parameters): Url` — Generates HTTP URL. Returns Singularity HttpUrl or throws exception.
- `clearDevCache(): void` — Clears development cache. No return value.
- `rebuildDevCache(): void` — Rebuilds development cache. No return value.

**Router Interface:**
- `matchIn(PsrRequest $request): ?Hit` — Matches incoming request. Returns Hit or null.
- `matchOut(string|LeafUrl $uri, ?array $parameters = null): ?Hit` — Matches outgoing URL. Returns Hit or null.

**Generator Interface:**
- `generateRoutes(): iterable<Route|Generator>` — Generates routes. Returns iterable of Route or Generator instances.

**Route Interface:**
- `matchIn(string $method, Uri $uri): ?Hit` — Matches incoming request. Returns Hit or null.
- `matchOut(string|LeafUrl $uri, ?array $parameters = null): ?Hit` — Matches outgoing URL. Returns Hit or null.
- `handleIn(PsrRequest $request, array $parameters, Archetype $archetype): PsrResponse` — Handles incoming request. Returns PSR-7 response.
- `parseParameters(): void` — Parses parameters from pattern. No return value.
- `addParameter(Parameter $parameter): static` — Adds parameter. Returns self for chaining.
- `getParameter(string $name): ?Parameter` — Gets parameter by name. Returns Parameter or null.
- `hasParameter(string $name): bool` — Checks if parameter exists. Returns bool.
- `removeParameter(string $name): static` — Removes parameter. Returns self for chaining.
- `forMethod(string ...$method): static` — Sets HTTP methods. Returns self for chaining.
- `hasMethod(string $method): bool` — Checks if method is set. Returns bool.
- `acceptsMethod(string $method): bool` — Checks if method is accepted. Returns bool.
- `removeMethod(string $method): static` — Removes method. Returns self for chaining.
- `fromArray(array $data, Archetype $archetype): static` — Creates route from array. Returns Route instance.

**Action Interface:**
- `getMiddleware(LeafRequest $request): ?MiddlewareProfile` — Gets middleware profile. Returns MiddlewareProfile or null.
- `scanSupportedMethods(): iterable<string>` — Scans supported HTTP methods. Returns iterable of method names.
- `execute(LeafRequest $request): mixed` — Executes action. Returns response (PSR-7 or transformable).

**Dispatcher Interface:**
- `handle(PsrRequest $request): PsrResponse` — Handles PSR-15 request. Returns PSR-7 response.

**Request Methods:**
- `__construct(LeafUrl $leafUrl, PsrRequest $httpRequest, array $parameters, Route $route): void` — Constructs request wrapper. No return value.
- `replaceHttpRequest(PsrRequest $httpRequest): void` — Replaces HTTP request. No return value.
- `hasParameter(string $name): bool` — Checks if parameter exists. Returns bool.
- `getParameter(string $name): mixed` — Gets parameter value. Returns mixed or null.

**Hit Methods:**
- `__construct(Route $route, array $parameters, ?string $queryString = null): void` — Constructs hit result. No return value.
- `getRoute(): Route` — Gets matched route. Returns Route instance.
- `getQueryString(): ?string` — Gets query string. Returns string or null.

**Parameter Methods:**
- `__construct(string $name, ?string $default = null): void` — Constructs parameter. No return value.
- `hasDefault(): bool` — Checks if default value exists. Returns bool.
- `isMultiSegment(): bool` — Checks if parameter is multi-segment. Returns bool.
- `getRegexFragment(): string` — Gets regex fragment for matching. Returns string.
- `validate(?string $value): bool` — Validates parameter value. Returns bool.
- `resolve(?string $value): mixed` — Resolves parameter value. Returns mixed.
- `fromArray(array $data, Archetype $archetype): Parameter` — Creates parameter from array. Returns Parameter instance.

**Pattern Methods:**
- `__construct(string $pattern): void` — Constructs pattern. No return value.
- `parseSegments(?Route $route = null): array<Segment>` — Parses pattern into segments. Returns array of Segment instances.
- `__toString(): string` — Converts pattern to string. Returns string.

**Segment Methods:**
- `fromString(int $index, string $segment, ?Route $route = null): static` — Creates segment from string. Returns Segment instance.
- `isDynamic(): bool` — Checks if segment is dynamic. Returns bool.
- `getParameterNames(): array<string>` — Gets parameter names in segment. Returns array of strings.
- `isWholeParameter(): bool` — Checks if segment is single parameter. Returns bool.
- `getParameters(): array<string,Parameter>` — Gets parameters in segment. Returns array of Parameter instances.
- `isMultiSegment(): bool` — Checks if segment is multi-segment. Returns bool.
- `match(string $part): ?array<?string>` — Matches segment against part. Returns parameter array or null.
- `compile(): string` — Compiles segment to regex. Returns string.
- `resolve(array $parameters): string` — Resolves segment with parameters. Returns string.
- `__toString(): string` — Converts segment to string. Returns string.

**LeafUrl Methods:**
- `fromString(string $uri): static` — Creates Leaf URL from string. Returns LeafUrl instance.
- `__construct(?string $path = null, ?string $query = null, ?string $fragment = null): void` — Constructs Leaf URL. No return value.
- `toClassName(): string` — Converts Leaf URL to class name. Returns string.
- `__toString(): string` — Converts Leaf URL to string. Returns string.

---

## 6. Error Handling

- Route matching failures return null (no match found)
- URL generation failures throw `RouteNotMatched` exception
- Action resolution failures throw Archetype exceptions
- Page action resolution failures throw `NotFound` exception (404)
- Parameter validation failures cause route match to fail
- Parameter resolution failures may cause route match to fail
- Pattern parsing failures throw `UnexpectedValue` exception
- Segment parsing failures throw `UnexpectedValue` exception
- Multi-segment parameter placement failures throw `UnexpectedValue` exception
- Route handling failures propagate exceptions
- Action execution failures may be caught and transformed to JSON responses
- Middleware execution failures propagate exceptions
- Response transformation failures propagate exceptions
- Generator failures may cause route generation to fail
- Router code generation failures may cause routing to fail
- Cache failures may cause performance degradation but not functional failures
- Leaf URL parsing failures throw `InvalidArgument` exception
- HTTP URL generation failures propagate exceptions
- Directory matching failures return null
- Trailing slash redirect failures return null
- Development cache clearing failures are ignored
- Development cache rebuilding failures are ignored
- Iota repository failures may cause caching to fail
- Archetype resolution failures throw Archetype exceptions
- Slingshot instantiation failures propagate exceptions
- Harvest middleware failures propagate exceptions
- Harvest transformation failures propagate exceptions
- Monarch path resolution failures may cause page loading to fail
- Atlas file operations failures may cause page loading to fail
- File existence checks may cause page loading to fail
- Middleware attribute parsing failures are ignored
- Route attribute parsing failures are ignored
- Parameter attribute parsing failures are ignored
- Action method scanning failures may cause method dispatch to fail
- Unknown method handling returns 405 response with Allow header
- Exception handling in actions supports JSON error responses
- Exception handling in actions supports HTML error responses
- Route serialization failures may cause caching to fail
- Route deserialization failures may cause route loading to fail

---

## 7. Configuration & Extensibility

- Router can be customized by implementing `Router` interface
- Generator can be customized by implementing `Generator` interface
- Route types can be customized by implementing `Route` interface
- Parameter types can be customized by extending `Parameter` class
- Action classes can be customized by implementing `Action` interface
- Page action handlers can be customized by implementing `PageAction` interface
- Middleware can be customized via `Middleware` attribute
- Route patterns support custom parameter types
- Route patterns support inset parameters (parameters within static segments)
- Route patterns support multi-segment parameters
- Route patterns support default parameter values
- Route patterns support HTTP method filtering
- Route patterns support query parameter matching
- Route patterns support fragment preservation
- Leaf URLs support path, query, and fragment components
- Leaf URLs support class name conversion
- HTTP URLs support full URL generation
- HTTP URLs support relative URL generation
- HTTP URLs support query parameter preservation
- HTTP URLs support fragment preservation
- Directory generator supports namespace mapping
- Directory generator supports priority ordering
- Pages generator supports priority ordering
- Collector generator supports custom generator registration
- PatternSwitch router supports code generation customization
- PatternSwitch router supports caching customization
- CheckEach router supports simple route iteration
- Route matching supports trailing slash handling
- Route matching supports directory matching
- Route matching supports redirect generation
- URL generation supports parameter resolution
- URL generation supports query string preservation
- URL generation supports fragment preservation
- Action execution supports middleware integration
- Action execution supports response transformation
- Action execution supports dependency injection
- ByMethodTrait supports method name derivation
- ByMethodTrait supports parameter-based method names
- ByMethodTrait supports unknown method handling
- PageActionTrait supports file scanning
- PageActionTrait supports pattern generation
- Html page action supports file loading
- Html page action supports content type setting
- Middleware attribute supports PSR-15 middleware
- Middleware attribute supports class name resolution
- Middleware attribute supports closure middleware
- Middleware attribute supports parameter passing
- Request wrapper supports HTTP request replacement
- Request wrapper supports parameter access
- Route serialization supports JSON export
- Route deserialization supports JSON import
- Parameter serialization supports JSON export
- Parameter deserialization supports JSON import
- Leaf URL supports string conversion
- Leaf URL supports class name conversion
- HTTP URL supports full URL generation
- Development cache clearing is automatic
- Development cache rebuilding is automatic
- Production cache is persistent
- Route generation is lazy
- Router code generation is lazy
- Route data caching is lazy

---

## 8. Interactions with Other Packages

### 8.1 Archetype

Greenleaf uses Archetype for:
- Resolving Action classes from Leaf URLs
- Resolving Generator classes from names
- Resolving PageAction classes from file extensions
- Namespace mapping for Action and Generator classes
- Class scanning for Action and Generator classes

Greenleaf registers Archetype resolvers for Generator and Action interfaces, allowing classes to be resolved from Leaf URLs and names.

### 8.2 Harvest

Greenleaf uses Harvest for:
- HTTP request/response handling
- Middleware integration
- Response transformation
- Middleware profile management
- Request/response interfaces

Greenleaf integrates with Harvest's middleware system and uses Harvest's response transformation for converting action outputs to PSR-7 responses.

### 8.3 Singularity

Greenleaf uses Singularity for:
- Leaf URL implementation
- HTTP URL generation
- URL parsing and manipulation
- Query parameter management
- Fragment handling

Greenleaf uses Singularity's Leaf URL scheme for internal route representation and converts to HTTP URLs for output.

### 8.4 Slingshot

Greenleaf uses Slingshot for:
- Dependency injection when instantiating actions
- Dependency injection when instantiating generators
- Parameter resolution in action methods
- Type binding for request objects

Greenleaf uses Slingshot to instantiate actions and generators with dependency injection, and to invoke action methods with resolved parameters.

### 8.5 Monarch

Greenleaf uses Monarch for:
- Global service location
- Path alias management (`@pages`)
- Environment detection (development vs production)
- Service container integration

Greenleaf uses Monarch to manage path aliases for page files and to detect development mode for cache management.

### 8.6 Iota

Greenleaf uses Iota for:
- Caching generated router code
- Caching route data
- Persistent storage of compiled routes

Greenleaf uses Iota to cache generated router code and route data for performance.

### 8.7 Kingdom

Greenleaf uses Kingdom for:
- Service container integration (implements `Service`)
- Service registration and resolution

### 8.8 Atlas

Greenleaf uses Atlas for:
- File system operations in page action handlers
- Directory scanning for page files
- File existence checks

### 8.9 Coercion

Greenleaf uses Coercion for:
- Type coercion in parameter resolution
- Value conversion and stringification

### 8.10 Dictum

Greenleaf uses Dictum for:
- Slug formatting in Slug parameter type
- ID formatting in Leaf URL to class name conversion

### 8.11 Exceptional

Greenleaf uses Exceptional for:
- Exception handling and creating route-related exceptions
- HTTP status code management

### 8.12 Nuance

Greenleaf uses Nuance for:
- Data inspection and entity rendering (Dumpable interface)
- Debugging and development tools

### 8.13 Other Packages

Greenleaf may be used by:
- `decodelabs/fabric` — Framework routing
- Applications built on Decode Labs ecosystem

Greenleaf may integrate with:
- `decodelabs/guidance` — UID route support (suggested)
- `decodelabs/horizon` — Page and Fragment component structures (via PageAction)

---

## 9. Usage Examples

### 9.1 Basic Routing

```php
use DecodeLabs\Greenleaf;
use DecodeLabs\Monarch;

$greenleaf = Monarch::getService(Greenleaf::class);
$dispatcher = $greenleaf->createDispatcher();
$response = $dispatcher->handle($request);
```

### 9.2 Route Generation

```php
namespace MyApp\Http;

use DecodeLabs\Greenleaf\Generator;
use DecodeLabs\Greenleaf\Route\Action;
use DecodeLabs\Greenleaf\Route\Parameter;

class Routes implements Generator
{
    public function generateRoutes(): iterable
    {
        // Basic route
        yield new Action('/', 'home');

        // Route with parameter
        yield new Action('test/{slug}', 'test', parameters: [
            new Parameter\Slug('slug')
        ]);

        // Route with inset parameter
        yield new Action('test-{slug}/', 'test?hello', parameters: [
            new Parameter\Slug('slug')
        ]);

        // Route with multi-part path parameter
        yield new Action('assets/{path}', 'assets', parameters: [
            new Parameter\Path('path')
        ]);

        // Redirect
        yield new Redirect('old/path', 'new/path');
    }
}
```

### 9.3 Action Implementation

```php
namespace MyApp\Http;

use DecodeLabs\Greenleaf\Action;
use DecodeLabs\Greenleaf\Action\ByMethodTrait;
use DecodeLabs\Greenleaf\Request as LeafRequest;
use DecodeLabs\Harvest\Response\Text as TextResponse;

class Test implements Action
{
    use ByMethodTrait;

    public function get(
        string $slug
    ): TextResponse {
        return new TextResponse('Get response: ' . $slug);
    }

    public function post(
        string $slug
    ): TextResponse {
        return new TextResponse('Post response: ' . $slug);
    }
}
```

### 9.4 URL Generation

```php
use DecodeLabs\Greenleaf;
use DecodeLabs\Monarch;

$greenleaf = Monarch::getService(Greenleaf::class);

// Generate URL from Leaf URL
$url = $greenleaf->url('leaf:/test?hello#fragment', ['slug' => 'my-slug']);
// https://mydomain.com/test/my-slug?hello#fragment

// Generate URL from string
$url = $greenleaf->url('test?hello', ['slug' => 'my-slug']);
// https://mydomain.com/test/my-slug?hello
```

### 9.5 Page Route

```php
namespace MyApp\Http;

use DecodeLabs\Greenleaf\Generator;
use DecodeLabs\Greenleaf\Route\Page;

class Routes implements Generator
{
    public function generateRoutes(): iterable
    {
        // HTML file /src/components/pages/about.html
        yield new Page('about', 'about.html');

        // Horizon Page /src/components/pages/blog.php
        yield new Page('blog', 'blog.php');
    }
}
```

### 9.6 Custom Parameter Type

```php
namespace MyApp\Http;

use DecodeLabs\Greenleaf\Route\Parameter;
use Attribute;

#[Attribute]
class Uid extends Parameter
{
    public function getRegexFragment(): string
    {
        return '(?P<' . $this->name . '>[a-zA-Z0-9]{32})';
    }

    public function validate(?string $value): bool
    {
        return $value !== null && strlen($value) === 32;
    }
}
```

### 9.7 Middleware on Action

```php
namespace MyApp\Http;

use DecodeLabs\Greenleaf\Action;
use DecodeLabs\Greenleaf\Action\ByMethodTrait;
use DecodeLabs\Greenleaf\Middleware;
use DecodeLabs\Greenleaf\Request as LeafRequest;
use Psr\Http\Server\MiddlewareInterface;

#[Middleware(MyMiddleware::class)]
class Test implements Action
{
    use ByMethodTrait;

    public function get(LeafRequest $request): mixed
    {
        // Middleware will be executed before this method
    }
}
```

### 9.8 Custom Router

```php
namespace MyApp\Http;

use DecodeLabs\Greenleaf\Router;
use DecodeLabs\Greenleaf\Route\Hit;
use DecodeLabs\Singularity\Url\Leaf as LeafUrl;
use Psr\Http\Message\ServerRequestInterface as PsrRequest;

class MyRouter implements Router
{
    public function matchIn(PsrRequest $request): ?Hit
    {
        // Custom matching logic
    }

    public function matchOut(string|LeafUrl $uri, ?array $parameters = null): ?Hit
    {
        // Custom URL matching logic
    }
}
```

### 9.9 Custom Generator

```php
namespace MyApp\Http;

use DecodeLabs\Greenleaf\Generator;
use DecodeLabs\Greenleaf\Route\Action;

class MyGenerator implements Generator
{
    public function generateRoutes(): iterable
    {
        yield new Action('/custom', 'custom');
    }
}
```

### 9.10 Route Matching

```php
use DecodeLabs\Greenleaf;
use DecodeLabs\Monarch;

$greenleaf = Monarch::getService(Greenleaf::class);

// Match incoming request
$hit = $greenleaf->matchIn($request);
if ($hit) {
    $route = $hit->getRoute();
    $parameters = $hit->parameters;
}

// Match outgoing URL
$hit = $greenleaf->matchOut('leaf:/test?hello', ['slug' => 'my-slug']);
$route = $hit->getRoute();
$parameters = $hit->parameters;
```

### 9.11 Parameter Types

```php
use DecodeLabs\Greenleaf\Route\Parameter;

// Slug parameter
new Parameter\Slug('slug');

// Path parameter (multi-segment)
new Parameter\Path('path');

// Number parameter
new Parameter\Number('id');

// Decimal parameter
new Parameter\Decimal('price');

// Pattern parameter
new Parameter\Pattern('code', '[A-Z]{3}');

// Options parameter
new Parameter\Options('status', ['active', 'inactive']);
```

### 9.12 Redirect with Query Mapping

```php
use DecodeLabs\Greenleaf\Route\Redirect;

// Redirect with query parameter mapping
yield new Redirect(
    pattern: 'old/path',
    target: 'new/path',
    permanent: true,
    mapQuery: ['page', 'sort'] // Map specific query parameters
);

// Redirect with all query parameters
yield new Redirect(
    pattern: 'old/path',
    target: 'new/path',
    mapQuery: true // Map all query parameters
);
```

---

## 10. Implementation Notes (for Contributors)

### 10.1 Internal Architecture

At a high level, Greenleaf:
- Provides PSR-15 middleware for request handling
- Uses generators to collect routes from various sources
- Uses routers to match requests to routes
- Uses routes to handle requests and generate URLs
- Uses Leaf URLs for internal route representation
- Uses HTTP URLs for external URL generation
- Integrates with Archetype for class resolution
- Integrates with Harvest for HTTP handling
- Integrates with Singularity for URL management
- Integrates with Slingshot for dependency injection
- Caches generated router code and route data via Iota
- Supports development mode cache clearing
- Supports production mode persistent caching

### 10.2 Request Flow

1. HTTP request arrives at Greenleaf middleware
2. Middleware calls `matchIn()` on Greenleaf service
3. Greenleaf service calls `matchIn()` on router
4. Router matches request path to route pattern
5. Router extracts parameters from path segments
6. Router validates parameters
7. Router returns Hit with matched route and parameters
8. Middleware calls `handleIn()` on matched route
9. Route resolves Action class via Archetype
10. Route instantiates Action via Slingshot
11. Route creates LeafRequest with parameters
12. Route calls `execute()` on Action
13. Action returns response (PSR-7 or transformable)
14. Route transforms response via Harvest if needed
15. Route executes middleware if present
16. Route returns PSR-7 response
17. Middleware returns response

### 10.3 URL Generation Flow

1. User calls `url()` on Greenleaf service with Leaf URL
2. Greenleaf service calls `matchOut()` on router
3. Router matches Leaf URL to route target
4. Router merges query parameters
5. Router returns Hit with matched route and parameters
6. Greenleaf service resolves parameters into path segments
7. Greenleaf service builds HTTP URL from pattern and parameters
8. Greenleaf service preserves query string and fragment
9. Greenleaf service returns Singularity HttpUrl

### 10.4 Route Generation Flow

1. Router or generator needs routes
2. Generator calls `generateRoutes()` on registered generators
3. Generators yield Route instances
4. Collector collects routes from all generators
5. Collector sorts routes by pattern specificity
6. Collector caches route data in Iota
7. Router uses cached route data for matching
8. PatternSwitch router generates optimized PHP code
9. PatternSwitch router caches generated code in Iota
10. Router uses cached code for matching

### 10.5 PatternSwitch Router

- Generates optimized PHP switch statements for route matching
- Creates nested switch statements for each path segment
- Handles dynamic segments with parameter extraction
- Handles static segments with direct matching
- Handles multi-segment parameters with greedy matching
- Generates code for both incoming and outgoing matching
- Caches generated code in Iota repository
- Rebuilds code when cache is cleared
- Handles development mode cache clearing
- Handles production mode persistent caching

### 10.6 CheckEach Router

- Iterates through all routes in sequence
- Checks each route for match
- Returns first matching route
- Simple and predictable behavior
- Not recommended for production use (slower)
- Useful for development and testing

### 10.7 Directory Generator

- Scans for Action classes via Archetype
- Scans for userland Generator classes
- Generates routes from Action class attributes
- Generates routes from userland generators
- Supports namespace mapping via Archetype
- Supports priority ordering
- Supports automatic route name generation
- Supports automatic method detection

### 10.8 Pages Generator

- Scans for PageAction implementations
- Generates routes from PageAction implementations
- Supports priority ordering
- Supports multiple page action handlers
- Supports file extension-based resolution

### 10.9 Collector Generator

- Collects routes from Directory and Pages generators
- Sorts routes by pattern specificity
- Caches route data in Iota
- Supports custom generator registration
- Supports priority ordering
- Supports nested generator scanning

### 10.10 Route Matching

- Validates HTTP method against route methods
- Parses path into segments
- Matches segments against pattern segments
- Extracts parameters from dynamic segments
- Validates parameters against parameter definitions
- Resolves parameters using parameter resolvers
- Supports default parameter values
- Supports multi-segment parameters
- Supports trailing slash handling
- Supports directory matching

### 10.11 URL Generation

- Matches Leaf URL to route target
- Merges query parameters
- Resolves parameters into path segments
- Builds HTTP URL from pattern and parameters
- Preserves query string and fragment
- Supports relative and absolute URLs
- Supports parameter resolution
- Supports query parameter preservation
- Supports fragment preservation

### 10.12 Action Execution

- Resolves Action class via Archetype
- Instantiates Action via Slingshot
- Creates LeafRequest with parameters
- Executes middleware if present
- Calls `execute()` on Action
- Transforms response via Harvest if needed
- Returns PSR-7 response

### 10.13 ByMethodTrait

- Scans for supported HTTP methods
- Dispatches to methods based on request method
- Supports method names with parameter suffixes
- Handles unknown methods with Allow header
- Uses Slingshot for dependency injection
- Supports exception handling
- Supports JSON error responses

### 10.14 PageActionTrait

- Scans page files from `@pages` path
- Converts file names to route patterns
- Supports file extension filtering
- Supports recursive directory scanning
- Supports index route handling

### 10.15 Parameter Types

- Base Parameter class provides validation and resolution
- Slug parameter validates URL-friendly format
- Path parameter captures multiple segments
- Number parameter validates digits
- Decimal parameter validates numeric format
- Pattern parameter uses custom regex
- Options parameter validates against allowed values
- Parameters support default values
- Parameters support validation
- Parameters support resolution
- Parameters support regex fragment generation

### 10.16 Leaf URL

- Represents internal route URLs with `leaf:` scheme
- Supports path, query, and fragment components
- Supports class name conversion
- Supports string conversion
- Used for route targets and matching
- Converted to HTTP URLs for output

### 10.17 Caching

- Router code is cached in Iota repository
- Route data is cached in Iota repository
- Development mode cache is cleared automatically
- Production mode cache is persistent
- Cache is rebuilt when cleared
- Cache is lazy-loaded (only when needed)

### 10.18 Gotchas & Historical Decisions

- PatternSwitch router generates PHP code dynamically
- PatternSwitch router code is cached in Iota
- Route matching is case-sensitive for paths
- Route matching is case-insensitive for HTTP methods
- Route matching supports OPTIONS and HEAD automatically
- Route matching supports trailing slash differences
- Route matching redirects trailing slash mismatches
- URL generation throws exception if no match found
- URL generation preserves query and fragment
- Action resolution uses Archetype namespace mapping
- Action resolution supports class name derivation
- Page action resolution uses file extension
- Page action resolution defaults to HTML
- Middleware execution uses Harvest dispatcher
- Response transformation uses Harvest transformer
- Exception handling supports JSON responses
- Exception handling supports HTML responses
- Development mode cache clearing is automatic
- Production mode caching is persistent
- Route generation is lazy
- Router code generation is lazy
- Route data caching is lazy
- Leaf URLs are used for internal representation
- HTTP URLs are used for external output
- Route patterns are normalized to start with `/`
- Route patterns support parameter placeholders
- Route patterns support inset parameters
- Route patterns support multi-segment parameters
- Route segments support dynamic and static tokens
- Route segments support multi-segment parameters
- Parameter validation happens during matching
- Parameter resolution happens during matching
- Default parameter values are used if missing
- Multi-segment parameters must be last segment
- Route serialization supports JSON export
- Route deserialization supports JSON import
- Directory generator scans for Action classes
- Directory generator scans for userland generators
- Pages generator scans for PageAction implementations
- Collector generator collects from multiple generators
- Collector generator sorts routes by specificity
- PatternSwitch router generates optimized code
- CheckEach router iterates through routes
- Middleware attribute supports PSR-15 middleware
- Middleware attribute supports class names
- Middleware attribute supports closures
- Request wrapper provides unified access
- Hit contains matched route and parameters
- Leaf URL parsing supports path, query, fragment
- Leaf URL to class name uses Dictum ID formatting
- HTTP URL generation uses Singularity HttpUrl
- HTTP URL generation resolves parameters
- Route matching handles trailing slash
- Route matching supports directory matching
- Action execution uses Slingshot
- Action execution supports middleware
- Action execution supports transformation
- ByMethodTrait dispatches by HTTP method
- ByMethodTrait supports parameter suffixes
- ByMethodTrait handles unknown methods
- PageActionTrait scans page files
- PageActionTrait generates patterns
- Html page action loads files
- Middleware integration uses Harvest
- Response transformation uses Harvest
- Exception handling supports JSON
- Exception handling supports HTML
- Development cache clearing is automatic
- Production cache is persistent
- Route generation is lazy
- Router code generation is lazy
- Route data caching is lazy

---

## 11. Testing & Quality

- **Code Quality Score:** 4.5/5
- **README Quality Score:** 4/5
- **Documentation Score:** 0/5 (this spec)
- **Test Coverage Score:** 0/5

See `composer.json` for supported PHP versions.

---

## 12. Roadmap & Future Ideas

- Enhanced documentation and examples
- Additional router implementations
- Additional parameter types
- Enhanced route generation
- Better caching strategies
- Enhanced URL generation
- Better error handling
- Enhanced middleware integration
- Additional page action handlers
- Better development tools
- Enhanced route debugging
- Additional route types
- Better performance optimizations
- Enhanced parameter validation
- Additional integration options

---

## 13. References

- [Archetype Package](https://github.com/decodelabs/archetype) — Class resolution
- [Harvest Package](https://github.com/decodelabs/harvest) — HTTP handling
- [Singularity Package](https://github.com/decodelabs/singularity) — URL management
- [Slingshot Package](https://github.com/decodelabs/slingshot) — Dependency injection
- [Monarch Package](https://github.com/decodelabs/monarch) — Global service location
- [Iota Package](https://github.com/decodelabs/iota) — Caching
- [Kingdom Package](https://github.com/decodelabs/kingdom) — Service container
- [Atlas Package](https://github.com/decodelabs/atlas) — File system operations
- [Coercion Package](https://github.com/decodelabs/coercion) — Type coercion
- [Dictum Package](https://github.com/decodelabs/dictum) — Text formatting
- [Exceptional Package](https://github.com/decodelabs/exceptional) — Exception handling
- [Nuance Package](https://github.com/decodelabs/nuance) — Data inspection
- [Fabric Package](https://github.com/decodelabs/fabric) — Framework using Greenleaf
- [Guidance Package](https://github.com/decodelabs/guidance) — UID route support (suggested)
- [Horizon Package](https://github.com/decodelabs/horizon) — Page components (optional)
- [PSR-15](https://www.php-fig.org/psr/psr-15/) — HTTP Server Middleware
- [PSR-7](https://www.php-fig.org/psr/psr-7/) — HTTP Message Interfaces
- [Chorus Package Index](../../../chorus/config/packages.json) — Ecosystem metadata

