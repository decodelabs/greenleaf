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
        if (defined('static::Middleware')) {
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
            $middleware = $attribute->getMiddleware();

            if (is_string($middleware)) {
                $output[$middleware] = $attribute->getParameters();
            } else {
                $output[] = $middleware;
            }
        }

        return $output;
    }



    /**
     * Prepare slingshot
     */
    protected function prepareSlingshot(
        array $parameters,
        LeafUrl $url,
        Request $request
    ): Slingshot {
        $output = new Slingshot(
            container: $this->context->container
        );

        $output->addParameters($request->getAttributes());
        $output->addParameters($request->getQueryParams());
        $output->addParameters($url->parseQuery()->toArray());
        $output->addParameters($parameters);

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
                $code = $e->getHttpStatus() ?? 500;
                $data = $e->getData();
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
