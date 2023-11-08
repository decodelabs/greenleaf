<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\Action;

use DecodeLabs\Harvest;
use DecodeLabs\Singularity\Url\Leaf as LeafUrl;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

trait ByMethodTrait
{
    use InvocationTrait;

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
            if ($method === 'HEAD') {
                $method = 'GET';
            } else {
                return $this->handleUnknownMethod($method);
            }
        }

        $method = strtolower($method);

        return $this->{$method}(...$this->prepareMethodParameters(
            method: $method,
            parameters: $parameters,
            url: $url,
            request: $request
        ));
    }

    /**
     * Handle unknown HTTP method
     */
    protected function handleUnknownMethod(
        string $method
    ): Response {
        $methods = [];

        foreach (HarvestRequest::METHODS as $testMethod) {
            if (method_exists($this, strtolower($testMethod))) {
                $methods[] = $testMethod;
            }
        }

        return Harvest::text('', $method === 'OPTIONS' ? 200 : 405, [
            'allow' => implode(', ', $methods)
        ]);
    }
}