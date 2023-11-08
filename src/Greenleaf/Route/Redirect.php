<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\Route;

use DecodeLabs\Greenleaf\Compiler\Pattern;
use DecodeLabs\Greenleaf\Route;
use DecodeLabs\Greenleaf\RouteTrait;
use DecodeLabs\Harvest;
use DecodeLabs\Singularity;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class Redirect implements Route
{
    use RouteTrait;

    protected string $target;
    protected bool $permanent = false;

    /**
     * @var bool|array<string>
     */
    protected bool|array $mapQuery = false;

    /**
     * Init with properties
     */
    public function __construct(
        string|Pattern $pattern,
        string $target
    ) {
        $this->pattern = $this->normalizePattern($pattern);
        $this->target = $target;
    }

    /**
     * Get target
     */
    public function getTarget(): string
    {
        return $this->target;
    }

    /**
     * Set permanent
     *
     * @return $this
     */
    public function setPermanent(
        bool $permanent
    ): static {
        $this->permanent = $permanent;
        return $this;
    }

    /**
     * Get permanent
     */
    public function isPermanent(): bool
    {
        return $this->permanent;
    }

    /**
     * Set map query
     *
     * @param bool|array<string> $map
     * @return $this
     */
    public function mapQuery(
        bool|array $map
    ): static {
        if (
            is_array($map) &&
            empty($map)
        ) {
            $map = false;
        }

        $this->mapQuery = $map;
        return $this;
    }

    /**
     * Should map query
     */
    public function isQueryMapped(): bool
    {
        return $this->mapQuery !== false;
    }

    /**
     * Handle request
     */
    public function handle(
        Request $request
    ): Response {
        $currentUrl = $request->getUri();
        $url = Singularity::url($this->target, $currentUrl);

        if ($this->mapQuery) {
            $query = $url->parseQuery();
            $current = Singularity::url($currentUrl)->parseQuery();

            if (is_array($this->mapQuery)) {
                foreach ($this->mapQuery as $key) {
                    if (
                        isset($current->{$key}) &&
                        !isset($query->{$key})
                    ) {
                        $query->{$key} = $current[$key];
                    }
                }
            } else {
                /** @var iterable<int|string, string|int|float|null> $query */
                $current->merge($query);
                $query = $current;
            }

            $url = $url->withQuery($query);
        }

        return Harvest::redirect(
            $url,
            $this->permanent ? 301 : 302
        );
    }
}
