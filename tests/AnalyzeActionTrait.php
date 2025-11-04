<?php

/**
 * Greenleaf
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\Tests;

use DecodeLabs\Greenleaf\Action;
use DecodeLabs\Greenleaf\ActionTrait;
use DecodeLabs\Greenleaf\Request as LeafRequest;
use DecodeLabs\Harvest\Response\Json as JsonResponse;
use Psr\Http\Message\ResponseInterface as PsrResponse;

class AnalyzeActionTrait implements Action
{
    use ActionTrait;

    public function execute(
        LeafRequest $request
    ): PsrResponse {
        return new JsonResponse(['foo' => 'bar']);
    }
}
