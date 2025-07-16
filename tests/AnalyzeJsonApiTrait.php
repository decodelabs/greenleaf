<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\Tests;

use DecodeLabs\Greenleaf\Action;
use DecodeLabs\Greenleaf\Action\JsonApiTrait;
use DecodeLabs\Harvest\Response\Json as JsonResponse;
use Psr\Http\Message\ResponseInterface as PsrResponse;

class AnalyzeJsonApiTrait implements Action
{
    use JsonApiTrait;

    public function get(): PsrResponse
    {
        return new JsonResponse(['foo' => 'bar']);
    }
}
