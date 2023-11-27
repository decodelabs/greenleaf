<?php
/**
 * This is a stub file for IDE compatibility only.
 * It should not be included in your projects.
 */
namespace DecodeLabs;

use DecodeLabs\Veneer\Proxy as Proxy;
use DecodeLabs\Veneer\ProxyTrait as ProxyTrait;
use DecodeLabs\Greenleaf\Context as Inst;
use DecodeLabs\Archetype\NamespaceMap as NamespacesPlugin;
use DecodeLabs\Archetype\Handler as ArchetypePlugin;
use DecodeLabs\Greenleaf\Router as RouterPlugin;
use DecodeLabs\Greenleaf\Dispatcher as Ref0;
use Psr\Http\Message\ServerRequestInterface as Ref1;
use DecodeLabs\Greenleaf\Compiler\Hit as Ref2;
use DecodeLabs\Singularity\Url\Leaf as Ref3;
use DecodeLabs\Singularity\Url as Ref4;
use Closure as Ref5;
use DecodeLabs\Greenleaf\Route\Action as Ref6;
use DecodeLabs\Greenleaf\Route\Redirect as Ref7;

class Greenleaf implements Proxy
{
    use ProxyTrait;

    const VENEER = 'DecodeLabs\\Greenleaf';
    const VENEER_TARGET = Inst::class;

    public static Inst $instance;
    public static NamespacesPlugin $namespaces;
    public static ArchetypePlugin $archetype;
    public static RouterPlugin $router;

    public static function createDispatcher(): Ref0 {
        return static::$instance->createDispatcher();
    }
    public static function matchIn(Ref1 $request, bool $checkDir = false): Ref2 {
        return static::$instance->matchIn(...func_get_args());
    }
    public static function matchOut(Ref3|string $uri, ?array $params = NULL): Ref2 {
        return static::$instance->matchOut(...func_get_args());
    }
    public static function createUrl(Ref3|string $uri, ?array $params = NULL): Ref4 {
        return static::$instance->createUrl(...func_get_args());
    }
    public static function route(string $pattern, ?string $target = NULL, ?Ref5 $setup = NULL): Ref6 {
        return static::$instance->route(...func_get_args());
    }
    public static function redirect(string $pattern, string $target, ?Ref5 $setup = NULL): Ref7 {
        return static::$instance->redirect(...func_get_args());
    }
};
