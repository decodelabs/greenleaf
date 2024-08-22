<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\Action;

use DecodeLabs\Greenleaf\ActionTrait;
use DecodeLabs\Greenleaf\Route;
use DecodeLabs\Harvest;
use DecodeLabs\Harvest\Request as HarvestRequest;
use DecodeLabs\Singularity\Url\Leaf as LeafUrl;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Throwable;

trait ByMethodTrait
{
    use ActionTrait;

    /**
     * Handle HTTP request
     */
    public function execute(
        Request $request,
        LeafUrl $url,
        array $parameters
    ): Response {
        if (!$method = $this->getMethod($request, $parameters)) {
            return $this->handleUnknownMethod($request);
        }

        try {
            return $this->prepareSlingshot(
                parameters: $parameters,
                url: $url,
                request: $request
            )->invoke([$this, $method]);
        } catch (Throwable $e) {
            return $this->handleException($e, $request);
        }
    }

    protected function getMethod(
        Request $request,
        array $parameters
    ): ?string {
        $method = $request->getMethod();
        $method = strtolower($method);

        $keys = array_keys($parameters);

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
        Request $request
    ): Response {
        return $this->handleUnknownMethod($request);
    }

    /**
     * Handle unknown HTTP method
     */
    protected function handleUnknownMethod(
        Request $request
    ): Response {
        $method = $request->getMethod();
        $methods = [];
        $route = $request->getAttribute('route');
        $functions = get_class_methods($this);

        foreach (HarvestRequest::Methods as $testMethod) {
            if (
                $route instanceof Route &&
                !$route->acceptsMethod($testMethod)
            ) {
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
