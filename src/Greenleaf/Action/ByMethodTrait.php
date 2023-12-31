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
        $method = $request->getMethod();

        if (!method_exists($this, $method)) {
            return $this->handleUnknownMethod($request);
        }

        $method = strtolower($method);

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

        foreach (HarvestRequest::METHODS as $testMethod) {
            if (
                !method_exists($this, strtolower($testMethod)) ||
                (
                    $route instanceof Route &&
                    !$route->acceptsMethod($testMethod)
                )
            ) {
                continue;
            }

            $methods[] = $testMethod;
        }

        return Harvest::text('', $method === 'OPTIONS' ? 200 : 405, [
            'Allow' => implode(', ', $methods),
            'Access-Control-Allow-Methods' => implode(', ', $methods),
        ]);
    }
}
