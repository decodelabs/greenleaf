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
use DecodeLabs\Greenleaf\Router as Ref0;
use DecodeLabs\Singularity\Url\Leaf as Ref1;
use DecodeLabs\Singularity\Url as Ref2;
use Closure as Ref3;
use DecodeLabs\Greenleaf\Route\Action as Ref4;
use DecodeLabs\Greenleaf\Route\Redirect as Ref5;

class Greenleaf implements Proxy
{
    use ProxyTrait;

    const VENEER = 'DecodeLabs\\Greenleaf';
    const VENEER_TARGET = Inst::class;

    public static Inst $instance;
    public static NamespacesPlugin $namespaces;

    public static function getRouter(): Ref0 {
        return static::$instance->getRouter();
    }
    public static function createUrl(Ref1|string $uri, ?array $params = NULL): Ref2 {
        return static::$instance->createUrl(...func_get_args());
    }
    public static function route(string $pattern, string $target, ?Ref3 $setup = NULL): Ref4 {
        return static::$instance->route(...func_get_args());
    }
    public static function redirect(string $pattern, string $target, ?Ref3 $setup = NULL): Ref5 {
        return static::$instance->redirect(...func_get_args());
    }
};
