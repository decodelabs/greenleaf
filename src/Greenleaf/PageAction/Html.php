<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\PageAction;

use DecodeLabs\Exceptional;
use DecodeLabs\Greenleaf\PageAction;
use DecodeLabs\Greenleaf\Action\ByMethodTrait;
use DecodeLabs\Greenleaf\Request as LeafRequest;
use DecodeLabs\Harvest;
use DecodeLabs\Harvest\Response;
use DecodeLabs\Monarch;
use Exception;

class Html implements PageAction
{
    use ByMethodTrait;

    public int $priority = 1;

    public function get(
        LeafRequest $request
    ): Response {
        $path = '@pages/'.ltrim($request->leafUrl->getPath(), '/');
        $resolvedPath = Monarch::$paths->resolve($path);

        if(!file_exists($resolvedPath)) {
            throw Exceptional::NotFound(
                message: 'Page not found: '.$path,
                http: 404
            );
        }

        return Harvest::stream($resolvedPath, headers: [
            'Content-Type' => 'text/html; charset=utf-8'
        ]);
    }
}
