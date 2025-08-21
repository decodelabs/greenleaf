<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\Route;

use Attribute;
use DecodeLabs\Archetype;
use DecodeLabs\Exceptional;
use DecodeLabs\Greenleaf\PageAction;
use DecodeLabs\Greenleaf\Request as LeafRequest;
use DecodeLabs\Greenleaf\Route;
use DecodeLabs\Slingshot;
use Psr\Http\Message\ResponseInterface as PsrResponse;
use Psr\Http\Message\ServerRequestInterface as PsrRequest;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_FUNCTION | Attribute::IS_REPEATABLE)]
class Page implements Route, Bidirectional
{
    use ActionTrait;

    public function handleIn(
        PsrRequest $request,
        array $parameters,
        Archetype $archetype
    ): PsrResponse {
        $type = $this->target->parsePath()?->getExtension();

        if ($type === null) {
            $this->target = $this->target->withPath(function ($path) {
                return $path?->withExtension('html') ?? '.html';
            });

            $type = 'html';
        }

        if (!$class = $archetype->tryResolve(
            interface: PageAction::class,
            names: ucfirst($type)
        )) {
            throw Exceptional::NotFound(
                message: 'No page handler for type "' . $type . '"',
                http: 404
            );
        }

        $slingshot = new Slingshot(archetype: $archetype);
        $action = $slingshot->newInstance($class);

        $leafRequest = new LeafRequest(
            httpRequest: $request,
            leafUrl: $this->target,
            parameters: $parameters,
            route: $this
        );

        return $this->dispatchAction(
            request: $leafRequest,
            action: $action,
            archetype: $archetype
        );
    }
}
