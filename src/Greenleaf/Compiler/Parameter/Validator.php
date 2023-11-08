<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\Compiler\Parameter;

interface Validator
{
    /**
     * Create validator from input
     *
     * @param string|array<string, mixed>|Validator|null $input
     */
    public static function create(
        string|array|Validator|null $input
    ): ?Validator;

    /**
     * Create from string
     */
    public static function fromString(
        string $input
    ): ?Validator;

    /**
     * Create from array
     *
     * @param array<string, mixed> $input
     */
    public static function fromArray(
        array $input
    ): ?Validator;


    public function getRegexFragment(): string;


    public function validate(
        ?string $value
    ): bool;

    public function resolve(
        ?string $value
    ): mixed;
}
