<?php

namespace NextDirection\Framework\Routing;

use NextDirection\Framework\Http\Request;

class Matcher {
    
    /**
     * @var Request
     */
    protected $request;
    
    /**
     * @var string
     */
    protected $matchedRoute;
    
    /**
     * @var string
     */
    protected $matchedHandler;
    
    /**
     * @var array
     */
    protected $routeParams;
    
    /**
     * @var array
     */
    protected $defaultValues;
    
    /**
     * @param Request $request
     */
    public function __construct(Request $request) {
        $this->request = $request;
    }
    
    /**
     * @return string
     */
    public function getMatchedRoute(): string {
        return $this->matchedRoute;
    }
    
    /**
     * @return string
     */
    public function getMatchedHandler(): string {
        return $this->matchedHandler;
    }
    
    /**
     * @return array
     */
    public function getRouteParameters(): array {
        return $this->routeParams;
    }
    
    /**
     * @return array
     */
    public function getDefaultValues(): array {
        return $this->defaultValues;
    }
    
    /**
     * Try to match called url with existing routes (first hit)
     *
     * @param Router $router
     *
     * @return bool - Match found
     */
    public function match(Router $router): bool {
        $method = $this->request->getMethod();
        $currentRoute = ltrim($this->request->getUrl(), '/');
        $definedRoutes = $router->getRoutes($method);
        $currentRouteParts = explode('/', $currentRoute);

        foreach ($definedRoutes as $definedRoute => $handlerMethod) {
            $definedRouteParts = explode('/', ltrim($definedRoute, '/'));
            $routeParameters = [];
            $defaultValues = [];
            $isLastPartOptional = false !== mb_strpos($definedRouteParts[count($definedRouteParts) - 1], '?');

            if (!$isLastPartOptional && count($definedRouteParts) !== count($currentRouteParts)) {
                continue;
            } else if (
                $isLastPartOptional
                &&  (
                    count($currentRouteParts) < count($definedRouteParts) - 1
                    || count($currentRouteParts) > count($definedRouteParts)
                )
            ) {
                continue;
            }

            foreach ($definedRouteParts as $index => $definedRoutePart) {
                $currentRoutePart = $currentRouteParts[$index] ?? '';
                
                if (':' !== mb_substr($definedRoutePart, 0, 1)) { // static part
                    
                    if ($currentRoutePart !== $definedRoutePart) { // if static part not matches, no hit
                        continue 2;
                    }
                } else { // dynamic value
                    $placeholderName = mb_substr($definedRoutePart, 1);
                    $regex = $defaultValue = '';
    
                    if (false !== mb_strpos($placeholderName, '<')) { // dynamic part has to match regex
                        list($placeholderName,) = explode('<', $placeholderName);
                        
                        if (preg_match('/<(.*)>/', $definedRoutePart, $regexMatch)) {
                            $regex = $regexMatch[1];
                        }
                    } else if (false !== mb_strpos($placeholderName, '?')) { // dynamic part is optional
                        list($placeholderName,) = explode('?', $placeholderName);
                    }

                    if (false !== mb_strpos($definedRoutePart, '?')) { // check if dynamic part has default value
                        $defaultValue = explode('?', $definedRoutePart)[1] ? : '';
                    }
    
                    // if dynamic part not matches regex, no hit (only if not empty, can only occur if last part is optional)
                    if ($regex && !preg_match('/' . $regex . '/', $currentRoutePart) && '' !== $currentRoutePart) {
                        continue 2;
                    }
                    
                    // don't set empty string (can only occur if last part is optional)
                    if ('' !== $currentRoutePart) {
                        $routeParameters[$placeholderName] = $currentRoutePart;
                    }
                    
                    if ('' !== $defaultValue) {
                        $defaultValues[$placeholderName] = $defaultValue;
                    }
                }
            }
            
            // if every part of the route matches, use first hit as match
            $this->routeParams = $routeParameters;
            $this->matchedHandler = $handlerMethod;
            $this->matchedRoute = $definedRoute;
            $this->defaultValues = $defaultValues;
            
            return true;
        }
        
        return false;
    }
}