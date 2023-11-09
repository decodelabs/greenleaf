<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf;

use DecodeLabs\Singularity\Url\Leaf as LeafUrl;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

interface Action
{
    public function __construct(
        Context $context
    );

    /**
     * @param array<string, mixed> $parameters
     */
    public function execute(
        Request $request,
        LeafUrl $url,
        array $parameters
    ): Response;
}
