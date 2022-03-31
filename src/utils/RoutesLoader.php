<?php
/**
 * Copyright (c) 2022.  <CubaDevOps>
 *
 * @Author : Carlos Batista <cbatista8a@gmail.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace CubaDevOps\utils;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Yaml\Yaml;

class RoutesLoader
{

    /**
     * @var string
     */
    private $config_path;

    public function __construct(string $config_path)
    {
        $this->config_path = $config_path;
    }

    /**
     * Loads a resource.
     *
     * @param string $resource The route.yml
     * @param bool $legacy_routes
     * @return array|RouteCollection
     */
    public function load($resource, $legacy_routes = false)
    {
        $routes = new RouteCollection();

        $routingFile = $this->config_path . '/' . $resource;
        if (file_exists($routingFile)) {
            $loadedRoutes = $this->yamlImport($routingFile);
            foreach ($loadedRoutes as $route_name => $front_route) {
                $route = new Route($front_route['path']);
                $route->setDefaults($front_route['defaults'])
                    ->setMethods($front_route['methods']);
                $routes->add($route_name, $route);
            }
        }

        return $legacy_routes ? $this->mapLegacyRoutesFromRoutesCollection($routes) : $routes;
    }

    public function yamlImport(string $routingFile)
    {
        return Yaml::parseFile($routingFile);
    }

    /**
     * @param \Symfony\Component\Routing\RouteCollection $routes_collection
     * @return array
     */
    private function mapLegacyRoutesFromRoutesCollection($routes_collection)
    {
        $legacy_routes = [];
        foreach ($routes_collection->getIterator() as $route_name => $new_route) {
            $legacy_routes[$route_name] = [
                'controller' => $new_route->getDefault('_controller'),
                'rule' => trim($new_route->getPath(), '/'),
                'keywords' => [],
                'params' => [
                    'fc' => 'module',
                    'module' => $new_route->getDefault('_module'),
                    'controller' => $new_route->getDefault('_legacy_controller') ?: $new_route->getDefault(
                        '_controller'
                    ),
                ]
            ];
        }
        return $legacy_routes;
    }
}