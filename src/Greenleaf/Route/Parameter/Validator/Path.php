<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\Route\Parameter\Validator;

use DecodeLabs\Greenleaf\Route\Parameter\ValidatorAbstract;

class Path extends ValidatorAbstract
{
    /**
     * Is multi segment
     */
    public function isMultiSegment(): bool
    {
        return true;
    }

    public function getRegexFragment(
        string $name
    ): string {
        return '(?P<' . $name . '>.+?)';
    }


    public function validate(
        ?string $value
    ): bool {
        return $value !== null;
    }

    public function resolve(
        ?string $value
    ): mixed {
        return $value;
    }
}
