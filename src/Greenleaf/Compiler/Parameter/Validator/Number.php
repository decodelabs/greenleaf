<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\Compiler\Parameter\Validator;

use DecodeLabs\Coercion;
use DecodeLabs\Greenleaf\Compiler\Parameter\ValidatorAbstract;

class Number extends ValidatorAbstract
{
    public function getRegexFragment(
        string $name
    ): string {
        return '(?P<' . $name . '>[0-9]+?)';
    }

    public function validate(
        ?string $value
    ): bool {
        return
            $value !== null &&
            ctype_digit($value);
    }

    public function resolve(
        ?string $value
    ): mixed {
        return Coercion::toIntOrNull($value);
    }
}
