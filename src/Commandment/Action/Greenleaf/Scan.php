<?php

/**
 * Greenleaf
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Commandment\Action\Greenleaf;

use DecodeLabs\Commandment\Action;
use DecodeLabs\Commandment\Request;
use DecodeLabs\Greenleaf;
use DecodeLabs\Monarch;
use DecodeLabs\Terminus\Session;

class Scan implements Action
{
    public function __construct(
        protected Session $io,
        protected Greenleaf $greenleaf
    ) {
    }

    public function execute(
        Request $request
    ): bool {
        if (!Monarch::isDevelopment()) {
            $this->io->error('This command is only available in development mode');
            return false;
        }

        $this->greenleaf->rebuildDevCache();
        $this->io->success('Dev cache rebuilt');

        return true;
    }
}
