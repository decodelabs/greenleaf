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
use DecodeLabs\Greenleaf\Context\Loader as LoaderPlugin;
use DecodeLabs\Greenleaf\Router as RouterPlugin;
use DecodeLabs\Veneer\Plugin\Wrapper as PluginWrapper;
use DecodeLabs\Greenleaf\Dispatcher as Ref0;
use DecodeLabs\Slingshot as Ref1;
use Psr\Http\Message\ServerRequestInterface as Ref2;
use DecodeLabs\Greenleaf\Route\Hit as Ref3;
use DecodeLabs\Singularity\Url\Leaf as Ref4;
use Stringable as Ref5;
use DecodeLabs\Singularity\Url as Ref6;
use DecodeLabs\Greenleaf\Route\Action as Ref7;
use DecodeLabs\Greenleaf\Route\Page as Ref8;
use DecodeLabs\Greenleaf\Route\Redirect as Ref9;

class Greenleaf implements Proxy
{
    use ProxyTrait;

    public const Veneer = 'DecodeLabs\\Greenleaf';
    public const VeneerTarget = Inst::class;

    protected static Inst $_veneerInstance;
    public static ArchetypePlugin $archetype;
    /** @var LoaderPlugin|PluginWrapper<LoaderPlugin> $loader */
    public static LoaderPlugin|PluginWrapper $loader;
    /** @var RouterPlugin|PluginWrapper<RouterPlugin> $router */
    public static RouterPlugin|PluginWrapper $router;

    public static function createDispatcher(): Ref0 {
        return static::$_veneerInstance->createDispatcher();
    }
    public static function newSlingshot(): Ref1 {
        return static::$_veneerInstance->newSlingshot();
    }
    public static function setDefaultPageType(string $type): void {}
    public static function getDefaultPageType(): string {
        return static::$_veneerInstance->getDefaultPageType();
    }
    public static function clearDevCache(): void {}
    public static function rebuildDevCache(): void {}
    public static function matchIn(Ref2 $request, bool $checkDir = false): ?Ref3 {
        return static::$_veneerInstance->matchIn(...func_get_args());
    }
    public static function matchOut(Ref4|string $uri, ?array $parameters = NULL): Ref3 {
        return static::$_veneerInstance->matchOut(...func_get_args());
    }
    public static function url(Ref4|string $uri, Ref5|string|int|float|null ...$parameters): Ref6 {
        return static::$_veneerInstance->url(...func_get_args());
    }
    public static function action(string $pattern, ?string $target = NULL): Ref7 {
        return static::$_veneerInstance->action(...func_get_args());
    }
    public static function page(string $pattern, ?string $target = NULL): Ref8 {
        return static::$_veneerInstance->page(...func_get_args());
    }
    public static function redirect(string $pattern, string $target): Ref9 {
        return static::$_veneerInstance->redirect(...func_get_args());
    }
};
