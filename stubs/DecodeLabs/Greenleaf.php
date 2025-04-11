<?php
/**
 * This is a stub file for IDE compatibility only.
 * It should not be included in your projects.
 */
namespace DecodeLabs;

use DecodeLabs\Veneer\Proxy as Proxy;
use DecodeLabs\Veneer\ProxyTrait as ProxyTrait;
use DecodeLabs\Greenleaf\Context as Inst;
use DecodeLabs\Archetype\Handler as ArchetypePlugin;
use DecodeLabs\Greenleaf\Router as RouterPlugin;
use DecodeLabs\Veneer\Plugin\Wrapper as PluginWrapper;
use DecodeLabs\Greenleaf\Dispatcher as Ref0;
use Psr\Http\Message\ServerRequestInterface as Ref1;
use DecodeLabs\Greenleaf\Compiler\Hit as Ref2;
use DecodeLabs\Singularity\Url\Leaf as Ref3;
use DecodeLabs\Singularity\Url as Ref4;
use DecodeLabs\Greenleaf\Route\Action as Ref5;
use DecodeLabs\Greenleaf\Route\Page as Ref6;
use DecodeLabs\Greenleaf\Route\Redirect as Ref7;

class Greenleaf implements Proxy
{
    use ProxyTrait;

    public const Veneer = 'DecodeLabs\\Greenleaf';
    public const VeneerTarget = Inst::class;

    protected static Inst $_veneerInstance;
    public static ArchetypePlugin $archetype;
    /** @var RouterPlugin|PluginWrapper<RouterPlugin> $router */
    public static RouterPlugin|PluginWrapper $router;

    public static function createDispatcher(): Ref0 {
        return static::$_veneerInstance->createDispatcher();
    }
    public static function setDefaultPageType(string $type): void {}
    public static function getDefaultPageType(): string {
        return static::$_veneerInstance->getDefaultPageType();
    }
    public static function matchIn(Ref1 $request, bool $checkDir = false): Ref2 {
        return static::$_veneerInstance->matchIn(...func_get_args());
    }
    public static function matchOut(Ref3|string $uri, ?array $parameters = NULL): Ref2 {
        return static::$_veneerInstance->matchOut(...func_get_args());
    }
    public static function createUrl(Ref3|string $uri, ?array $parameters = NULL): Ref4 {
        return static::$_veneerInstance->createUrl(...func_get_args());
    }
    public static function action(string $pattern, ?string $target = NULL): Ref5 {
        return static::$_veneerInstance->action(...func_get_args());
    }
    public static function page(string $pattern, ?string $target = NULL): Ref6 {
        return static::$_veneerInstance->page(...func_get_args());
    }
    public static function redirect(string $pattern, string $target): Ref7 {
        return static::$_veneerInstance->redirect(...func_get_args());
    }
};
