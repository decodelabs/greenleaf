<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf;

use DecodeLabs\Exceptional;
use DecodeLabs\Glitch\Proxy as GlitchProxy;
use DecodeLabs\Greenleaf\Attribute\Middleware;
use DecodeLabs\Harvest;
use DecodeLabs\Harvest\Request as HarvestRequest;
use DecodeLabs\Pandora\Container as PandoraContainer;
use DecodeLabs\Singularity\Url\Leaf as LeafUrl;
use DecodeLabs\Slingshot;
use Psr\Container\ContainerInterface as Container;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use ReflectionClass;
use Throwable;

/**
 * @phpstan-require-implements Action
 */
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
     * Get middleware list
     */
    public function getMiddleware(): ?array
    {
        if (!empty(static::Middleware)) {
            return static::Middleware;
        }

        $ref = new ReflectionClass($this);
        $attributes = $ref->getAttributes(Middleware::class);

        if (empty($attributes)) {
            return null;
        }

        $output = [];

        foreach ($attributes as $attribute) {
            $attribute = $attribute->newInstance();
            $middleware = $attribute->middleware;

            if (is_string($middleware)) {
                $output[$middleware] = $attribute->parameters;
            } else {
                $output[] = $middleware;
            }
        }

        // @phpstan-ignore-next-line
        return $output;
    }



    /**
     * Prepare slingshot
     *
     * @param array<string,mixed> $parameters
     */
    protected function prepareSlingshot(
        array $parameters,
        LeafUrl $url,
        Request $request
    ): Slingshot {
        $output = new Slingshot(
            container: $this->context->container
        );

        /** @var array<string,mixed> */
        $attributes = $request->getAttributes();
        /** @var array<string,mixed> */
        $queryParams = $request->getQueryParams();
        /** @var array<string,mixed> */
        $urlQuery = $url->parseQuery()->toArray();

        $output->addParameters($attributes);
        $output->addParameters($queryParams);
        $output->addParameters($urlQuery);
        $output->addParameters($parameters);

        // @phpstan-ignore-next-line
        $output->addTypes([
            LeafUrl::class => $url,
            Request::class => $request,
            Container::class => $this->context->container
        ]);

        if ($request instanceof HarvestRequest) {
            $output->addType(
                $request,
                HarvestRequest::class
            );
        }

        if ($this->context->container instanceof PandoraContainer) {
            $output->addType(
                $this->context->container,
                PandoraContainer::class
            );
        }

        return $output;
    }

    /**
     * Handle exception
     */
    protected function handleException(
        Throwable $e,
        Request $request
    ): Response {
        if (
            $request->getHeaderLine('Accept') === 'application/json' ||
            $this->getDefaultContentType() === 'application/json'
        ) {
            GlitchProxy::logException($e);

            if ($e instanceof Exceptional\Exception) {
                $code = $e->http ?? 500;
                $data = $e->data;
            } else {
                $code = 500;
                $data = null;
            }

            return Harvest::json([
                'error' => $e->getMessage(),
                'data' => $data
            ], $code);
        }

        throw $e;
    }

    /**
     * Get default content type
     */
    protected function getDefaultContentType(): string
    {
        if (
            defined('static::ContentType') &&
            is_string(static::ContentType)
        ) {
            return static::ContentType;
        }

        return 'text/html';
    }
}
