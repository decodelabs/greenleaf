<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\Tests;

use DecodeLabs\Greenleaf\Action;
use DecodeLabs\Greenleaf\ActionTrait;
use DecodeLabs\Harvest\Response\Json as JsonResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use DecodeLabs\Singularity\Url\Leaf as LeafUrl;


class AnalyzeActionTrait implements Action
{
    use ActionTrait;

    public function execute(
        Request $request,
        LeafUrl $uri,
        array $parameters
    ): Response {
        return new JsonResponse(['foo' => 'bar']);
    }
}
