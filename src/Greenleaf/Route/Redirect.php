<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\Route;

use Attribute;
use DecodeLabs\Greenleaf\Context;
use DecodeLabs\Greenleaf\Route;
use DecodeLabs\Greenleaf\RouteTrait;
use DecodeLabs\Greenleaf\Route\Pattern;
use DecodeLabs\Harvest;
use DecodeLabs\Singularity;
use Psr\Http\Message\ResponseInterface as PsrResponse;
use Psr\Http\Message\ServerRequestInterface as PsrRequest;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_FUNCTION | Attribute::IS_REPEATABLE)]
class Redirect implements Route
{
    use RouteTrait;

    public string $target;
    public bool $permanent = false;

    /**
     * @var bool|array<string>
     */
    protected bool|array $mapQuery = false;

    /**
     * @param array{
     *     pattern:string|Pattern,
     *     target:string,
     *     permanent?:bool,
     *     mapQuery?:bool|array<string>
     * } $data
     */
    public static function fromArray(
        array $data
    ): static {
        return new static(
            pattern: $data['pattern'],
            target: $data['target'],
            permanent: $data['permanent'] ?? false,
            mapQuery: $data['mapQuery'] ?? false
        );
    }

    /**
     * Init with properties
     *
     * @param bool|array<string> $mapQuery
     */
    final public function __construct(
        string|Pattern $pattern,
        string $target,
        bool $permanent = false,
        bool|array $mapQuery = false
    ) {
        $this->pattern = $this->normalizePattern($pattern);
        $this->target = $target;
        $this->permanent = $permanent;
        $this->mapQuery($mapQuery);
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
    public function handleIn(
        Context $context,
        PsrRequest $request,
        array $parameters
    ): PsrResponse {
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

    /**
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->exportData([
            'target' => $this->target,
            'permanent' => $this->permanent,
        ]);
    }
}
