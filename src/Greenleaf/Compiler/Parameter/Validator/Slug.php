<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\Compiler\Parameter\Validator;

use DecodeLabs\Dictum;
use DecodeLabs\Greenleaf\Compiler\Parameter\ValidatorAbstract;

class Slug extends ValidatorAbstract
{
    public function getRegexFragment(
        string $name
    ): string {
        return '(?P<' . $name . '>[a-z0-9\-_]+?)';
    }


    public function validate(
        ?string $value
    ): bool {
        return $value !== null;
    }

    public function resolve(
        ?string $value
    ): mixed {
        return Dictum::slug($value);
    }
}
