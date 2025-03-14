<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\Tests;

use DecodeLabs\Greenleaf\Action;
use DecodeLabs\Greenleaf\ActionTrait;
use DecodeLabs\Greenleaf\Request as LeafRequest;
use DecodeLabs\Harvest\Response\Json as JsonResponse;
use Psr\Http\Message\ResponseInterface as Response;


class AnalyzeActionTrait implements Action
{
    use ActionTrait;

    public function execute(
        LeafRequest $request
    ): Response {
        return new JsonResponse(['foo' => 'bar']);
    }
}
