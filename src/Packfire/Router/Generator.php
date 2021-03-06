<?php /*
 * Copyright (C) 2014 Sam-Mauris Yong. All rights reserved.
 * This file is part of the Packfire Router project, which is released under New BSD 3-Clause license.
 * See file LICENSE or go to http://opensource.org/licenses/BSD-3-Clause for full license details.
 */

namespace Packfire\Router;

use Packfire\Router\Matchers\PathMatcher;
use Packfire\Router\Exceptions\MissingRequiredParameterException;

class Generator implements GeneratorInterface
{
    /**
     * Generate the URL given a route and its parameters
     * @param  Packfire\Router\RouteInterface $route The route the generate the URL from
     * @param  array $params The parameters of the route
     * @return string Returns the generated URL.
     */
    public function generate(RouteInterface $route, $params)
    {
        $rules = $route->rules();
        if (isset($rules['path'])) {
            $path = $rules['path'];
            if (isset($path['uri'])) {
                $uri = $path['uri'];
                $tokens = PathMatcher::createTokens($uri);

                if ($tokens) {
                    $replacements = array();
                    foreach ($tokens as $token) {
                        $name = $token[3];
                        if (isset($params[$name])) {
                            $replacements[$token[0]] = $token[2] . $params[$name];
                        } elseif ($token[4] == '?') {
                            $replacements[$token[0]] = '';
                        } else {
                            throw new MissingRequiredParameterException($route->name(), $name);
                        }
                    }
                    $uri = str_replace(array_keys($replacements), $replacements, $uri);
                }
                return $uri;
            }
        }
    }
}
