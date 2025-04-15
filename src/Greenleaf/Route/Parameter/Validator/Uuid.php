<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\Route\Parameter\Validator;

use DecodeLabs\Greenleaf\Route\Parameter\ValidatorAbstract;
use DecodeLabs\Guidance;

class Uuid extends ValidatorAbstract
{
    public function getRegexFragment(
        string $name
    ): string {
        return '(?P<' . $name . '>([0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}+)?)';
    }


    public function validate(
        ?string $value
    ): bool {
        if (class_exists(Guidance::class)) {
            return Guidance::isValid($value);
        }

        return $value !== null;
    }

    public function resolve(
        ?string $value
    ): mixed {
        if ($value === null) {
            return null;
        }

        if (class_exists(Guidance::class)) {
            return Guidance::from($value);
        }

        return $value;
    }
}
