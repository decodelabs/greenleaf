<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\Compiler;

use DecodeLabs\Greenleaf\Compiler\Parameter\Validator;
use DecodeLabs\Greenleaf\Compiler\Parameter\ValidatorAbstract;

class Parameter
{
    protected readonly string $name;
    protected ?Validator $validator;
    protected ?string $default = null;

    /**
     * Init with properties
     *
     * @param string|array<string, mixed>|Validator|null $validate
     */
    public function __construct(
        string $name,
        string|array|Validator|null $validate = null,
        ?string $default = null
    ) {
        $this->name = $name;
        $this->validator = ValidatorAbstract::create($validate);
        $this->default = $default;
    }


    /**
     * Get name
     */
    public function getName(): string
    {
        return $this->name;
    }


    /**
     * Set validator
     *
     * @return $this
     */
    public function setValidator(
        ?Validator $validator
    ): static {
        $this->validator = $validator;
        return $this;
    }

    /**
     * Get validator
     */
    public function getValidator(): ?Validator
    {
        return $this->validator;
    }

    /**
     * Set default
     *
     * @return $this
     */
    public function setDefault(
        ?string $default
    ): static {
        $this->default = $default;
        return $this;
    }

    /**
     * Get default
     */
    public function getDefault(): ?string
    {
        return $this->default;
    }

    /**
     * Has default
     */
    public function hasDefault(): bool
    {
        return $this->default !== null;
    }


    /**
     * Is multi segment
     */
    public function isMultiSegment(): bool
    {
        if (!$this->validator) {
            return false;
        }

        return $this->validator->isMultiSegment();
    }


    /**
     * Get regex fragment
     */
    public function getRegexFragment(): string
    {
        if ($this->validator) {
            return $this->validator->getRegexFragment($this->name);
        }

        return '(?P<' . $this->name . '>\w+)';
    }


    /**
     * Validate value
     */
    public function validate(
        ?string $value
    ): bool {
        if ($value === null) {
            if ($this->default === null) {
                return false;
            }

            $value = $this->default;
        }

        if ($this->validator) {
            return $this->validator->validate($value);
        }

        return true;
    }

    /**
     * Resolve value
     */
    public function resolve(
        ?string $value
    ): mixed {
        if ($value === null) {
            $value = $this->default;
        }

        if ($this->validator) {
            $value = $this->validator->resolve($value);
        }

        return $value;
    }
}
