<?php

namespace NextDirection\Framework\Routing;

use NextDirection\Framework\Common\DirectoryInspection;
use NextDirection\Framework\Config\Reader;
use NextDirection\Framework\Config\Types;
use NextDirection\Framework\Http\RequestMethods;

class Router {
    
    /**
     * @var array
     */
    protected $routes = [];
    
    public function __construct() {
        $this->collectAnnotationRoutes();
    }
    
    /**
     * @param string $method
     *
     * @return array
     */
    public function getRoutes(string $method = RequestMethods::GET): array {
        
        if (!RequestMethods::isValid($method)) {
            throw new \InvalidArgumentException('Unsupported request method');
        }
        
        return array_key_exists($method, $this->routes) ? $this->routes[$method] : [];
    }
    
    /**
     * Collects all routes from controller
     */
    protected function collectAnnotationRoutes(): void {
        $appConfig = new Reader(Types::APP);
        $fullQualifiedClassNames = DirectoryInspection::getFullQualifiedClassNames($appConfig->get('controllerDirectory'));
        
        $prefixRegEx = '/@RoutePrefix=(.*)\s/m';
        $routeRegEx = '/@Route=(.*)\s/m';
        $methodRegEx = '/@Method=(.*)\s/m';

        foreach ($fullQualifiedClassNames as $className) {
            
            try {
                $reflectionClass = new \ReflectionClass($className);
                
                $prefix = preg_match($prefixRegEx, $reflectionClass->getDocComment(), $prefixMatch) ? $prefixMatch[1] : '';
                
                foreach ($reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $reflectionMethod) {
                    $docComment = $reflectionMethod->getDocComment();
                    $method = preg_match($methodRegEx, $docComment, $methodMatch) ? $methodMatch[1] : 'GET';
                    preg_match($routeRegEx, $docComment, $routeMatch);
                    
                    $route = $routeMatch[1];
                    
                    if (null !== $route) {
                        
                        if (!isset($this->routes[$method])) {
                            $this->routes[$method] = [];
                        }
                        
                        $fullRoute = str_replace('//', '/', $prefix . $route);
                        $fullRoute = mb_strlen($fullRoute) > 1 ? rtrim($fullRoute, '/') : $fullRoute;
                        
                        if ($this->checkRouteValidity($fullRoute)) {
                            $this->routes[$method][$fullRoute] = $className . '::' . $reflectionMethod->getName();
                        }
                    }
                }
            } catch (\Throwable $e) {
            }
        }
    }
    
    /**
     * Check if defined route is valid (creates WARNING if not)
     *
     * @param string $route
     *
     * @return bool
     */
    protected function checkRouteValidity(string $route): bool {
        $fullRouteParts = explode('/', rtrim($route, '/'));
        $placeholder = [];
        $routeValid = true;
        
        foreach ($fullRouteParts as $index => $routePart) {
            
            if (false !== mb_strpos($routePart, '?') && isset($fullRouteParts[$index + 1])) {
                trigger_error('Optional route part can only be the last one (' . $route . ')!', E_USER_WARNING);
                $routeValid = false;
                continue;
            }
            
            if (false !== mb_strpos($routePart, '?') && preg_match('/<(.*)>/', $routePart, $match)) {
                $regex = $match[1];
                list(, $defaultValue) = explode('?', $routePart);
                
                if ('' === $regex) {
                    trigger_error('Empty pattern not allowed (' . $route . ')!', E_USER_WARNING);
                    $routeValid = false;
                    continue;
                }
                
                if ('' !== $defaultValue && !preg_match('/' . $regex . '/', $defaultValue)) {
                    trigger_error('Default value doesn\'t match the pattern (' . $route . ')!', E_USER_WARNING);
                    $routeValid = false;
                    continue;
                }
            }
            
            if (0 === mb_strpos($routePart, ':')) {
                $routePart = mb_substr($routePart, 1);
                
                if (false !== mb_strpos($routePart, '<')) {
                    list($placeholderName,) = explode('<', $routePart);
                } else if (false !== mb_strpos($routePart, '?')) {
                    list($placeholderName,) = explode('?', $routePart);
                } else {
                    $placeholderName = $routePart;
                }
                
                if (in_array($placeholderName, $placeholder)) {
                    trigger_error('Duplicate placeholder name ' . $placeholderName . ' (' . $route . ')!', E_USER_WARNING);
                    $routeValid = false;
                    continue;
                }
    
                $placeholder[] = $placeholderName;
            } else if (preg_match('/[^a-z\-0-9]/', $routePart)) {
                trigger_error('Route part includes invalid characters (' . $route . ')!', E_USER_WARNING);
                $routeValid = false;
                continue;
            }
        }
        
        return $routeValid;
    }
}