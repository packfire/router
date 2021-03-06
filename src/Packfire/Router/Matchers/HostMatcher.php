<?php /*
 * Copyright (C) 2014 Sam-Mauris Yong. All rights reserved.
 * This file is part of the Packfire Router project, which is released under New BSD 3-Clause license.
 * See file LICENSE or go to http://opensource.org/licenses/BSD-3-Clause for full license details.
 */

namespace Packfire\Router\Matchers;

use Packfire\Router\RouteInterface;

class HostMatcher extends AbstractMatcher
{
    public function match(RouteInterface $route)
    {
        $result = true;
        $rules = $route->rules();
        if (isset($rules['host'])) {
            $hosts = (array)$rules['host'];
            foreach ($hosts as $host) {
                $result = (bool)preg_match(self::regexCompiler($host), $this->request->host());
                if ($result) {
                    break;
                }
            }
        }
        return $result;
    }

    public static function regexCompiler($host)
    {
        return '`^' . str_replace(array('\*', '\?'), array('[a-z0-9]+', '.+'), preg_quote($host, '`')) . '$`i';
    }
}
