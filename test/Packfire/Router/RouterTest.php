<?php /*
 * Copyright (C) 2014 Sam-Mauris Yong. All rights reserved.
 * This file is part of the Packfire Router project, which is released under New BSD 3-Clause license.
 * See file LICENSE or go to http://opensource.org/licenses/BSD-3-Clause for full license details.
 */

namespace Packfire\Router;

use \PHPUnit_Framework_TestCase;
use Packfire\FuelBlade\Container;

class RouterTest extends PHPUnit_Framework_TestCase
{
    public function testRoute()
    {
        $config = array(
            'path' => '/test',
            'target' => 'http://heartcode.sg/'
        );

        $router = new Router();
        $router->add('test', $config);

        $request = new CurrentRequest(
            array(
                'SCRIPT_NAME' => '/index.php',
                'PHP_SELF' => '/index.php/test'
            )
        );

        $route = $router->route($request);
        $this->assertInstanceOf('Packfire\\Router\\RouteInterface', $route);
        $this->assertEquals('test', $route->name());
    }

    public function testRouteMatchMany()
    {
        $router = new Router();

        $config = array(
            'path' => '/test',
            'target' => 'http://heartcode.sg/'
        );
        $router->add('test', $config);

        $config = array(
            'path' => '/blog-:id',
            'target' => 'http://heartcode.sg/'
        );
        $router->add('blog', $config);

        $request = new CurrentRequest(
            array(
                'SCRIPT_NAME' => '/index.php',
                'PHP_SELF' => '/index.php/blog-5'
            )
        );

        $route = $router->route($request);
        $this->assertInstanceOf('Packfire\\Router\\RouteInterface', $route);
        $this->assertEquals('blog', $route->name());
    }

    public function testRouteMatchNone()
    {
        $router = new Router();

        $config = array(
            'path' => '/test',
            'target' => 'http://heartcode.sg/'
        );
        $router->add('test', $config);

        $config = array(
            'path' => '/blog-:id',
            'params' => array('id' => 'i'),
            'target' => 'http://heartcode.sg/'
        );
        $router->add('blog', $config);

        $request = new CurrentRequest(
            array(
                'SCRIPT_NAME' => '/index.php',
                'PHP_SELF' => '/index.php/blog-tw'
            )
        );

        $route = $router->route($request);
        $this->assertNull($route);
    }

    public function testRouteCustomMatcher()
    {
        $container = new Container();

        $container['Packfire\\Router\\MatcherInterface'] = 'Packfire\\Router\\Matchers\\MethodMatcher';

        $router = $container->instantiate('Packfire\\Router\\Router');

        $config = array(
            'path' => '/test',
            'method' => 'POST',
            'target' => 'http://heartcode.sg/'
        );
        $router->add('test', $config);

        $config = array(
            'path' => '/test',
            'method' => array('get', 'post'),
            'target' => 'http://heartcode.sg/'
        );
        $router->add('test2', $config);

        $request = new CurrentRequest(
            array(
                'SCRIPT_NAME' => '/index.php',
                'PHP_SELF' => '/index.php/test',
                'REQUEST_METHOD' => 'get'
            )
        );

        $route = $router->route($request);
        $this->assertInstanceOf('Packfire\\Router\\RouteInterface', $route);
        $this->assertEquals('test2', $route->name());
    }

    public function testCustomFactory()
    {
        $route = $this->getMock('Packfire\\Router\\RouteInterface');

        $factory = $this->getMock('Packfire\\Router\\RouteFactoryInterface');
        $factory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($route));

        $container = new Container();
        $container['Packfire\\Router\\RouteFactoryInterface'] = $factory;

        $router = $container->instantiate('Packfire\\Router\\Router');

        $router->add('test');

        $testRoute = $router->route($this->getMock('Packfire\\Router\\RequestInterface'));
        $this->assertEquals($route, $testRoute);
    }

    public function testGenerate()
    {
        $config = array(
            'path' => '/blog/:id',
            'target' => 'http://heartcode.sg/'
        );

        $router = new Router();
        $router->add('test', $config);

        $uri = $router->generate('test', array('id' => 5));
        $this->assertEquals('/blog/5', $uri);
    }

    public function testGenerateCustomGenerator()
    {
        $generator = $this->getMock('Packfire\\Router\\GeneratorInterface');
        $generator->expects($this->once())
            ->method('generate')
            ->will($this->returnValue('/blog/5'));

        $container = new Container();
        $container['Packfire\\Router\\GeneratorInterface'] = $generator;

        $router = $container->instantiate('Packfire\\Router\\Router');

        $config = array(
            'path' => '/blog/:id',
            'target' => 'http://heartcode.sg/'
        );
        $router->add('test', $config);

        $uri = $router->generate('test', array('id' => 5));
        $this->assertEquals('/blog/5', $uri);
    }

    /**
     * @expectedException Packfire\Router\Exceptions\RouteNotFoundException
     */
    public function testGenerateException()
    {
        $config = array(
            'path' => '/blog/:id',
            'target' => 'http://heartcode.sg/'
        );

        $router = new Router();
        $router->add('one', $config);
        $router->generate('two');
    }
}
