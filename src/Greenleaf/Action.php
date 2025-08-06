<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf;

use DecodeLabs\Greenleaf\Request as LeafRequest;
use DecodeLabs\Harvest\Profile as MiddlewareProfile;

interface Action
{
    public function __construct(
        Context $context
    );

    public function getMiddleware(
        LeafRequest $request
    ): ?MiddlewareProfile;

    /**
     * @return iterable<string>
     */
    public function scanSupportedMethods(): iterable;

    public function execute(
        LeafRequest $request
    ): mixed;
}
