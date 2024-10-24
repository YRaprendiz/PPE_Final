<?php
class Router {
    private $routes = [];
    
    public function add($method, $path, $controller, $action) {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'controller' => $controller,
            'action' => $action
        ];
    }
    
    public function dispatch($method, $uri) {
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $this->matchPath($route['path'], $uri)) {
                $controller = new $route['controller']();
                call_user_func_array([$controller, $route['action']], $this->getParams($route['path'], $uri));
                return;
            }
        }
        throw new Exception('Route not found');
    }
    
    private function matchPath($routePath, $uri) {
        $routeSegments = explode('/', trim($routePath, '/'));
        $uriSegments = explode('/', trim($uri, '/'));
        
        if (count($routeSegments) !== count($uriSegments)) {
            return false;
        }
        
        foreach ($routeSegments as $i => $segment) {
            if ($segment[0] === ':') {
                continue;
            }
            if ($segment !== $uriSegments[$i]) {
                return false;
            }
        }
        
        return true;
    }
    
    private function getParams($routePath, $uri) {
        $params = [];
        $routeSegments = explode('/', trim($routePath, '/'));
        $uriSegments = explode('/', trim($uri, '/'));
        
        foreach ($routeSegments as $i => $segment) {
            if ($segment[0] === ':') {
                $params[] = $uriSegments[$i];
            }
        }
        
        return $params;
    }
}