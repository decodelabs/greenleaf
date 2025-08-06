<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\Action;

use Closure;
use DecodeLabs\Greenleaf\ActionTrait;
use DecodeLabs\Greenleaf\Request as LeafRequest;
use DecodeLabs\Harvest;
use DecodeLabs\Harvest\Request as HarvestRequest;
use Psr\Http\Message\ResponseInterface as PsrResponse;
use Throwable;

trait ByMethodTrait
{
    use ActionTrait;

    public function scanSupportedMethods(): iterable
    {
        $output = [];
        $classMethods = get_class_methods($this);

        foreach (HarvestRequest::Methods as $method) {
            $method = strtolower($method);

            if (method_exists($this, $method)) {
                $output[] = $method;
                continue;
            }

            foreach ($classMethods as $classMethod) {
                if (str_starts_with($classMethod, $method . 'By')) {
                    $output[] = $method;
                    continue 2;
                }
            }
        }

        return $output;
    }

    /**
     * Handle HTTP request
     */
    public function execute(
        LeafRequest $request
    ): mixed {
        if (!$method = $this->getMethod($request)) {
            return $this->handleUnknownMethod($request);
        }

        try {
            /** @var Closure():PsrResponse $callback */
            $callback = $this->{$method}(...);
            return $this->prepareSlingshot($request)->invoke($callback);
        } catch (Throwable $e) {
            return $this->handleException($e, $request);
        }
    }

    protected function getMethod(
        LeafRequest $request
    ): ?string {
        $method = $request->httpRequest->getMethod();
        $method = strtolower($method);

        $keys = array_keys($request->parameters);

        while (!empty($keys)) {
            $function = $method . 'By' . implode('', array_map('ucfirst', $keys));

            if (method_exists($this, $function)) {
                return $function;
            }

            array_pop($keys);
        }

        if (method_exists($this, $method)) {
            return $method;
        }

        return null;
    }


    /**
     * Handle HTTP OPTIONS request
     */
    public function options(
        LeafRequest $request
    ): mixed {
        return $this->handleUnknownMethod($request);
    }

    /**
     * Handle unknown HTTP method
     */
    protected function handleUnknownMethod(
        LeafRequest $request
    ): mixed {
        $method = $request->httpRequest->getMethod();
        $methods = [];
        $functions = get_class_methods($this);

        foreach (HarvestRequest::Methods as $testMethod) {
            if (!$request->route->acceptsMethod($testMethod)) {
                continue;
            }

            $testMethodLower = strtolower($testMethod);

            if (in_array($testMethodLower, $functions)) {
                $methods[] = $testMethod;
                continue;
            }

            foreach ($functions as $function) {
                if (str_starts_with($function, $testMethodLower . 'By')) {
                    continue;
                }

                $methods[] = $testMethod;
                continue 2;
            }
        }

        return Harvest::text('', $method === 'OPTIONS' ? 200 : 405, [
            'Allow' => implode(', ', $methods),
            'Access-Control-Allow-Methods' => implode(', ', $methods),
        ]);
    }
}
