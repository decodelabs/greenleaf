<?php

/**
 * Greenleaf
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\Route;

use Attribute;
use DecodeLabs\Archetype;
use DecodeLabs\Greenleaf\Action as ActionInterface;
use DecodeLabs\Greenleaf\Request as LeafRequest;
use DecodeLabs\Greenleaf\Route;
use DecodeLabs\Slingshot;
use Psr\Http\Message\ResponseInterface as PsrResponse;
use Psr\Http\Message\ServerRequestInterface as PsrRequest;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_FUNCTION | Attribute::IS_REPEATABLE)]
class Action implements Route, Bidirectional
{
    use ActionTrait;

    public function handleIn(
        PsrRequest $request,
        array $parameters,
        Archetype $archetype
    ): PsrResponse {
        $class = $archetype->resolve(ActionInterface::class, (string)$this->target);
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
