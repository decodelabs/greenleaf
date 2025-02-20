<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\Compiler\Parameter;

use DecodeLabs\Archetype;
use DecodeLabs\Coercion;

abstract class ValidatorAbstract implements Validator
{
    /**
     * Create validator from input
     *
     * @param string|array<string, mixed>|Validator|null $input
     */
    public static function create(
        string|array|Validator|null $input
    ): ?Validator {
        if ($input === null) {
            return null;
        }

        if ($input instanceof Validator) {
            return $input;
        }

        if (is_string($input)) {
            return static::fromString($input);
        }

        return static::fromArray($input);
    }

    /**
     * Create from string
     */
    public static function fromString(
        string $input
    ): ?Validator {
        $setup = [];

        // Regex
        if (preg_match('|^/.+/([a-z]+)?$|', $input, $matches)) {
            $setup['pattern'] = $matches[1] ?? null;
        }

        // Type
        else {
            $setup['as'] = $input;
        }

        return static::fromArray($setup);
    }

    /**
     * Create from array
     *
     * @param array<string, mixed> $input
     */
    public static function fromArray(
        array $input
    ): ?Validator {
        $type = $input['as'] ?? null;
        unset($input['as']);

        if (isset($input['pattern'])) {
            $type = 'regex';
        }

        if ($type === null) {
            $type = 'text';
        }

        $class = Archetype::resolve(
            Validator::class,
            ucfirst(Coercion::asString($type))
        );

        return new $class(...$input);
    }

    /**
     * Is multi segment
     */
    public function isMultiSegment(): bool
    {
        return false;
    }
}
