<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\Router\PatternSwitch;

use DecodeLabs\Coercion;
use DecodeLabs\Greenleaf\Route;
use DecodeLabs\Greenleaf\Route\Bidirectional;
use DecodeLabs\Hatch;

class OutMap
{
    /**
     * @var array<string,OutGroup>
     */
    public protected(set) array $groups = [];

    public function mapRoute(
        Route $route
    ): void {
        if (!$route instanceof Bidirectional) {
            return;
        }

        $path = $route->target->getPath();

        if (!isset($this->groups[$path])) {
            $this->groups[$path] = new OutGroup();
        }

        $group = $this->groups[$path];
        $group->mapRoute($route);
    }

    public function generateSwitches(): string
    {
        $cases = [];

        foreach ($this->groups as $path => $group) {
            $groupData = [];
            $routeData = [];

            foreach ($group->routes as $route) {
                $queryKeys = $route->target->parseQuery()->getKeys();
                $pattern = (string)$route->pattern;
                $id = uniqid('||route-', true) . '||';

                $groupData[$pattern] = [
                    'queryKeys' => $queryKeys,
                    'paramNames' => array_keys($route->parameters),
                    'route' => $id
                ];

                $routeData[$id] = $route->jsonSerialize();
            }

            $groupDataString = Hatch::exportStaticArray($groupData);

            /** @var array<string,string|array<mixed>> $route */
            foreach ($routeData as $id => $route) {
                $class = Coercion::asString($route['class']);
                unset($route['class']);
                $routeString = Hatch::exportStaticArray($route);

                $routeString =
                    <<<PHP
                    fn() => \\$class::fromArray({$routeString})
                    PHP;

                $routeString = str_replace("\n", "\n        ", $routeString);
                $groupDataString = str_replace("'$id'", $routeString, $groupDataString);
            }

            $groupDataString = str_replace("\n", "\n    ", $groupDataString);

            $cases[] =
                <<<PHP
                case '$path':
                    \$groupData = {$groupDataString};
                    break;
                PHP;
        }

        $cases[] =
            <<<PHP
            default:
                return null;
            PHP;

        $caseString = str_replace("\n", "\n    ", implode("\n\n", $cases));

        return
            <<<PHP
            \$groupData = [];
            switch(\$uri->getPath()) {
                {$caseString}
            }
            \$origQuery = \$uri->parseQuery();
            foreach(\$groupData as \$group) {
                foreach(\$group['queryKeys'] as \$key) {
                    if(!isset(\$origQuery->{\$key})) {
                        continue 2;
                    }
                }
                \$query = clone \$origQuery;
                \$params = [];
                foreach(\$query as \$key => \$node) {
                    if(!is_string(\$key) || !\$node->hasValue()) {
                        continue;
                    }
                    if(in_array(\$key, \$group['queryKeys'])) {
                        unset(\$query->{\$key});
                        continue;
                    }
                    if(in_array(\$key, \$group['paramNames'])) {
                        \$params[\$key] = \$node->getValue();
                        unset(\$query->{\$key});
                        continue;
                    }
                    continue 2;
                }
                \$params = array_merge(\$params, \$parameters);
                \$route = \$group['route']();
                foreach(\$route->parameters as \$name => \$parameter) {
                    if(isset(\$params[\$name])) {
                        continue;
                    }

                    if(\$parameter->hasDefault()) {
                        \$params[\$name] = \$parameter->default;
                        continue;
                    }

                    continue 2;
                }
                return new Hit(\$route, \$params, \$query->toDelimitedString());
            }
            return null;
            PHP;
    }
}
