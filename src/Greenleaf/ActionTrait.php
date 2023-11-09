<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf;

use DecodeLabs\Harvest\Request as HarvestRequest;
use DecodeLabs\Singularity\Url\Leaf as LeafUrl;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UriInterface as Uri;
use ReflectionClass;
use ReflectionNamedType;

trait ActionTrait
{
    protected Context $context;

    /**
     * Init with Context
     */
    public function __construct(
        Context $context
    ) {
        $this->context = $context;
    }


    /**
     * Prepare method parameters
     *
     * @return array<string, mixed>
     */
    protected function prepareMethodParameters(
        string $method,
        array $parameters,
        LeafUrl $url,
        Request $request
    ): array {
        $ref = new ReflectionClass($this);
        $method = $ref->getMethod($method);
        $params = $method->getParameters();
        $query = $url->parseQuery();
        $queryParams = $request->getQueryParams();
        $output = [];

        foreach ($params as $param) {
            $name = $param->getName();

            // Parameters passed in from route
            if (isset($parameters[$name])) {
                $output[$name] = $parameters[$name];
                continue;
            }

            // Leaf URL query
            if (isset($query[$name])) {
                $output[$name] = $query[$name];
                continue;
            }

            // PSR-7 query
            if (array_key_exists($name, $queryParams)) {
                $output[$name] = $queryParams[$name];
                continue;
            }

            // Type
            if (
                ($type = $param->getType()) &&
                $type instanceof ReflectionNamedType
            ) {
                switch ($type) {
                    case HarvestRequest::class:
                        if (!$request instanceof HarvestRequest) {
                            break;
                        }

                        // no break
                    case Request::class:
                        $output[$name] = $request;
                        continue 2;

                    case LeafUrl::class:
                        $output[$name] = $url;
                        continue 2;

                    case Uri::class:
                        $output[$name] = $request->getUri();
                        continue 2;
                }
            }

            // Default value
            if ($param->isDefaultValueAvailable()) {
                $output[$name] = $param->getDefaultValue();
                continue;
            }

            // Optional parameter
            if ($param->isOptional()) {
                $output[$name] = null;
                continue;
            }

            throw Exceptional::UnexpectedValue(
                'Method "' . $method->getName() . '" missing required parameter: ' . $name
            );
        }

        return $output;
    }
}
