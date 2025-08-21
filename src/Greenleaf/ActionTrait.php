<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf;

use DecodeLabs\Exceptional;
use DecodeLabs\Greenleaf\Request as LeafRequest;
use DecodeLabs\Harvest\Profile as MiddlewareProfile;
use DecodeLabs\Harvest\Request as HarvestRequest;
use DecodeLabs\Harvest\Response\Json as JsonResponse;
use DecodeLabs\Harvest\Stage\Deferred as DeferredStage;
use DecodeLabs\Monarch;
use DecodeLabs\Singularity\Url\Leaf as LeafUrl;
use DecodeLabs\Slingshot;
use Psr\Http\Message\ResponseInterface as PsrResponse;
use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use ReflectionAttribute;
use ReflectionClass;
use Throwable;

/**
 * @phpstan-require-implements Action
 */
trait ActionTrait
{
    public function getMiddleware(
        LeafRequest $request
    ): ?MiddlewareProfile {
        $attributes = $this->getMiddlewareAttributes($request);

        if (empty($attributes)) {
            return null;
        }

        $output = new MiddlewareProfile();

        foreach ($attributes as $attribute) {
            $attribute = $attribute->newInstance();
            $middleware = $attribute->middleware;

            if (is_string($middleware)) {
                $output->add(new DeferredStage(
                    $middleware,
                    parameters: $attribute->parameters
                ));
            } else {
                $output->add($middleware);
            }
        }

        return $output;
    }

    public function scanSupportedMethods(): iterable
    {
        return HarvestRequest::Methods;
    }

    /**
     * @return array<ReflectionAttribute<Middleware>>
     */
    protected function getMiddlewareAttributes(
        LeafRequest $request
    ): array {
        $ref = new ReflectionClass($this);
        return $ref->getAttributes(Middleware::class);
    }


    protected function prepareSlingshot(
        LeafRequest $request
    ): Slingshot {
        $output = new Slingshot();

        /** @var array<string,mixed> */
        $attributes = $request->httpRequest->getAttributes();
        /** @var array<string,mixed> */
        $queryParams = $request->httpRequest->getQueryParams();
        /** @var array<string,mixed> */
        $urlQuery = $request->leafUrl->parseQuery()->toArray();

        $output->addParameters($attributes);
        $output->addParameters($queryParams);
        $output->addParameters($urlQuery);
        $output->addParameters($request->parameters);

        // @phpstan-ignore-next-line
        $output->addTypes([
            LeafRequest::class => $request,
            LeafUrl::class => $request->leafUrl,
            PsrRequest::class => $request->httpRequest,
        ]);

        if ($request->httpRequest instanceof HarvestRequest) {
            $output->addType(
                $request->httpRequest,
                HarvestRequest::class
            );
        }

        return $output;
    }

    protected function handleException(
        Throwable $e,
        LeafRequest $request
    ): PsrResponse {
        if (
            $request->httpRequest->getHeaderLine('Accept') === 'application/json' ||
            $this->getDefaultContentType() === 'application/json'
        ) {
            Monarch::logException($e);

            if ($e instanceof Exceptional\Exception) {
                $code = $e->http ?? 500;
                $data = $e->data;
            } else {
                $code = 500;
                $data = null;
            }

            return new JsonResponse([
                'error' => $e->getMessage(),
                'data' => $data
            ], $code);
        }

        throw $e;
    }

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
