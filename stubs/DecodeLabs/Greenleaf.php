<?php
/**
 * This is a stub file for IDE compatibility only.
 * It should not be included in your projects.
 */
namespace DecodeLabs;

use DecodeLabs\Veneer\Proxy as Proxy;
use DecodeLabs\Veneer\ProxyTrait as ProxyTrait;
use DecodeLabs\Greenleaf\Context as Inst;
use Closure as Ref0;
use DecodeLabs\Greenleaf\Route\Action as Ref1;
use DecodeLabs\Greenleaf\Route\Redirect as Ref2;

class Greenleaf implements Proxy
{
    use ProxyTrait;

    const VENEER = 'DecodeLabs\\Greenleaf';
    const VENEER_TARGET = Inst::class;

    public static Inst $instance;

    public static function route(string $pattern, string $target, ?Ref0 $setup = NULL): Ref1 {
        return static::$instance->route(...func_get_args());
    }
    public static function redirect(string $pattern, string $target, ?Ref0 $setup = NULL): Ref2 {
        return static::$instance->redirect(...func_get_args());
    }
};
