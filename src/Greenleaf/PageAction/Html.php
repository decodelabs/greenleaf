<?php

/**
 * Greenleaf
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\PageAction;

use DecodeLabs\Exceptional;
use DecodeLabs\Greenleaf\Action\ByMethodTrait;
use DecodeLabs\Greenleaf\PageAction;
use DecodeLabs\Greenleaf\PageActionTrait;
use DecodeLabs\Greenleaf\Request as LeafRequest;
use DecodeLabs\Greenleaf\Route\Page as PageRoute;
use DecodeLabs\Harvest\Response\Stream as StreamResponse;
use DecodeLabs\Monarch;

class Html implements PageAction
{
    use ByMethodTrait;
    use PageActionTrait;

    public int $priority = 1;

    public function get(
        LeafRequest $request
    ): StreamResponse {
        $path = '@pages/' . ltrim($request->leafUrl->getPath(), '/');
        $resolvedPath = Monarch::getPaths()->resolve($path);

        if (!file_exists($resolvedPath)) {
            throw Exceptional::NotFound(
                message: 'Page not found: ' . $path,
                http: 404
            );
        }

        return new StreamResponse($resolvedPath, headers: [
            'Content-Type' => 'text/html; charset=utf-8'
        ]);
    }

    public function generateRoutes(): iterable
    {
        foreach ($this->scanPageFiles('html') as $name => $file) {
            yield new PageRoute(
                pattern: $this->nameToPattern($name),
                target: $name . '.html'
            );
        }
    }
}
