<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\Router\PatternSwitch;

use DecodeLabs\Greenleaf\Route;
use DecodeLabs\Greenleaf\Route\Segment;
use DecodeLabs\Hatch;

class InStep
{
    public ?Segment $segment;

    /**
     * @var array<string,InStep>
     */
    public array $steps = [];

    /**
     * @var array<string,Route>
     */
    public array $routes = [];

    public function __construct(
        ?Segment $segment = null
    ) {
        $this->segment = $segment;
    }

    /**
     * @param array<Segment> $segments
     */
    public function mapSegments(
        array $segments,
        Route $route
    ): void {
        $segment = array_shift($segments);

        if ($segment === null) {
            $this->routes[(string)$route->pattern] = $route;
            return;
        }

        $segmentString = (string)$segment;

        if (isset($this->steps[$segmentString])) {
            $step = $this->steps[$segmentString];
        } else {
            $this->steps[$segmentString] = $step = new self($segment);
        }

        if (empty($segments)) {
            $step->routes[(string)$route->pattern] = $route;
        } else {
            $step->mapSegments(
                $segments,
                $route
            );
        }
    }

    public function isDynamic(): bool
    {
        return
            $this->segment !== null &&
            $this->segment->isDynamic();
    }

    public function generateSwitches(): string
    {
        $cases = $dynamics = [];
        $singleDynamic = '';

        foreach ($this->steps as $key => $step) {
            if (!$step->segment) {
                continue;
            }

            $switchString = str_replace("\n", "\n    ", $step->generateSwitches());

            if ($step->isDynamic()) {
                $regex = $step->segment->compile();

                $paramString = '';
                $paramNames = $step->segment->getParameterNames();
                $paramNamesString = implode(', ', array_map(
                    static function ($name) {
                        return "'$name'";
                    },
                    $paramNames
                ));

                if (!empty($paramNames)) {
                    $paramString =
                        <<<PHP
                        foreach([{$paramNamesString}] as \$name) {
                            \$params[\$name] = \$matches[\$name];
                        }
                        PHP;
                }

                $paramString = str_replace("\n", "\n    ", $paramString);

                if ($step->segment->isMultiSegment()) {
                    $partPrefix =
                        <<<PHP
                        \$multiParts = \$parts;
                        array_unshift(\$multiParts, \$part);
                        \$multiPart = implode('/', \$multiParts);
                        PHP;
                    $part = '$multiPart';
                } else {
                    $partPrefix = '';
                    $part = '$part';
                }

                $singleDynamic =
                    <<<PHP
                    {$partPrefix}
                    if (preg_match('$regex', $part, \$matches)) {
                        {$paramString}
                        {$switchString}
                    }
                    PHP;

                $paramString = str_replace("\n", "\n    ", $paramString);
                $switchString = str_replace("\n", "\n    ", $switchString);

                $dynamics[] =
                    <<<PHP
                    {$partPrefix}
                    if (preg_match('$regex', $part, \$matches)) {
                        \$hit = (function(\$parts) use (\$matches, \$method, \$params) {
                            {$paramString}
                            {$switchString}
                        })(\$parts);
                        if(\$hit !== null) {
                            return \$hit;
                        }
                    }
                    PHP;
            } else {
                $cases[] =
                    <<<PHP
                    case '$key':
                        {$switchString}
                        break;
                    PHP;
            }
        }

        $nullOption = '';

        if (
            !empty($this->routes) ||
            !empty($dynamics)
        ) {
            $routes = [];

            foreach ($this->routes as $route) {
                $methods = $route->methods;
                $routeClass = get_class($route);

                /** @var array<string,string|array<mixed>> $routeData */
                $routeData = $route->jsonSerialize();
                unset($routeData['class']);
                $routeArgs = Hatch::exportStaticArray($routeData);
                $defaults = [];

                foreach ($route->parameters as $name => $parameter) {
                    if ($parameter->hasDefault()) {
                        $defaults[$name] = $parameter->default;
                    }
                }

                $defaultsString = Hatch::exportStaticArray($defaults);

                if (!empty($defaults)) {
                    $paramsString =
                        <<<PHP
                        array_merge({$defaultsString}, \$params)
                        PHP;
                } else {
                    $paramsString =
                        <<<PHP
                        \$params
                        PHP;
                }

                $hitString =
                    <<<PHP
                    return new Hit(\\{$routeClass}::fromArray({$routeArgs}), {$paramsString});
                    PHP;

                if (empty($methods)) {
                    $routeString = $hitString;
                    break;
                }

                $methodsString = implode(', ', array_map(
                    static function ($method) {
                        return "'$method'";
                    },
                    $methods
                ));

                $hitString = str_replace("\n", "\n    ", $hitString);

                $routes[] =
                    <<<PHP
                    if(in_array(\$method, [{$methodsString}])) {
                        {$hitString}
                    }
                    PHP;
            }

            $routeString = implode("\n", $routes);

            if ($this->segment?->isMultiSegment()) {
                $nullOption =
                    <<<PHP
                    {$routeString}
                    PHP;
            } else {
                $routeString = str_replace("\n", "\n    ", $routeString);

                $nullOption =
                    <<<PHP
                    if(\$part === null) {
                        {$routeString}
                        return null;
                    }
                    PHP;
            }
        }

        if (count($dynamics) === 1) {
            $dynamicString = $singleDynamic;
        } else {
            $dynamicString = str_replace("\n", "\n    ", implode("\n", $dynamics));
        }

        if (empty($cases)) {
            return
                <<<PHP
                \$part = array_shift(\$parts);
                {$nullOption}
                {$dynamicString}
                return null;
                PHP;
        }

        $cases[] =
            <<<PHP
            default:
                {$dynamicString}
                return null;
            PHP;

        $caseString = str_replace("\n", "\n    ", implode("\n\n", $cases));

        return
            <<<PHP
            \$part = array_shift(\$parts);
            {$nullOption}
            switch(\$part) {
                {$caseString}
            }
            PHP;
    }
}
