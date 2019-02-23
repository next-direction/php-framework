<?php

namespace NextDirection\Framework\Routing;

use NextDirection\Framework\Config\Reader;
use NextDirection\Framework\Config\Types;
use NextDirection\Framework\Di\ObjectFactory;
use NextDirection\Framework\Http\Response;
use NextDirection\Framework\Http\ResponseCodes;
use NextDirection\Framework\Security\EntityInterface;
use NextDirection\Framework\Security\ProviderInterface;

class Dispatcher {
    
    /**
     * @var ProviderInterface
     */
    protected $securityProvider;
    
    /**
     * @var Reader
     */
    protected $diConfig;
    
    /**
     *
     * @param ProviderInterface $securityProvider
     */
    public function __construct(ProviderInterface $securityProvider) {
        $this->securityProvider = $securityProvider;
        $this->diConfig = (new Reader(Types::DI))->get();
    }
    
    /**
     * Create controller instance, inject dependencies, call action method
     *
     * @param Matcher $matcher
     */
    public function dispatch(Matcher $matcher): void {
        list($fullQualifiedClassName, $handlerMethodName) = explode('::', $matcher->getMatchedHandler());
        $routeParameter = $matcher->getRouteParameters();
        $defaultValues = $matcher->getDefaultValues();

        $handler = ObjectFactory::createInstance($fullQualifiedClassName);
        $arguments = [];
        
        try {
            $reflectionClass = new \ReflectionClass($fullQualifiedClassName);
            $docComment = $reflectionClass->getDocComment();
            
            // permission denied on controller level
            if ($docComment && ResponseCodes::HTTP_OK !== ($statusCode = $this->checkPermission($docComment))) {
                /** @var Response $response */
                $response = ObjectFactory::createInstance(Response::class);
                $response
                    ->setCode($statusCode)
                    ->send();
                
                return;
            }
            
            $reflectionHandlerMethod = $reflectionClass->getMethod($handlerMethodName);
            $docComment = $reflectionHandlerMethod->getDocComment();
    
            // permission denied on handler method
            if ($docComment && ResponseCodes::HTTP_OK !== ($statusCode = $this->checkPermission($docComment))) {
                /** @var Response $response */
                $response = ObjectFactory::createInstance(Response::class);
                $response
                    ->setCode($statusCode)
                    ->send();
        
                return;
            }
            
            $reflectionArguments = $reflectionHandlerMethod->getParameters();
            
            foreach ($reflectionArguments as $reflectionArgument) {
            
                if ($class = $reflectionArgument->getClass()) {
    
                    if ($class->isInterface()) {
                        $reader = new Reader(Types::DI);
                        $className = $reader->get($class->getName());
        
                        if ($className) {
                            $arguments[] = ObjectFactory::createInstance($className);
                        } else {
                            throw new \RuntimeException('No class found for injection!');
                        }
                    } else {
                        $arguments[] = ObjectFactory::createInstance($class->getName());
                    }
                } else {
                    $parameterName = $reflectionArgument->getName();
                    
                    if (isset($routeParameter[$parameterName])) {
                        $arguments[] = $routeParameter[$parameterName];
                    } else if (isset($defaultValues[$parameterName])) {
                        $arguments[] = $defaultValues[$parameterName];
                    } else if ($defaultValue = $reflectionArgument->getDefaultValue()) {
                        $arguments[] = $defaultValue;
                    } else {
                        throw new \RuntimeException('Parameter could not be injected');
                    }
                }
            }
        } catch (\ReflectionException $e) {}

        try {
            $response = call_user_func_array([$handler, $handlerMethodName], $arguments);
        } catch (\Exception $e) {
            /** @var Response $response */
            $response = ObjectFactory::createInstance(Response::class);
            $response
                ->setCode(ResponseCodes::HTTP_INTERNAL_SERVER_ERROR)
                ->setBody($e->getMessage());
        }
        
        if ($response instanceof Response) {
            $response->send();
        } else {
            /** @var Response $responseObject */
            $responseObject = ObjectFactory::createInstance(Response::class);
            $responseObject
                ->setBody((string) $response)
                ->send();
        }
    }
    
    /**
     * @param string $docComment
     *
     * @return int - HTTP status code (200 if ok, 401 or 403 on error)
     */
    protected function checkPermission(string $docComment): int {
        
        if (preg_match('/Roles=(.*)/', $docComment, $rolesMatch)) {
            $roles = array_filter(explode(',', $rolesMatch[1]));
            
            if ($roles) {
                
                if (!isset($this->diConfig[EntityInterface::class])) {
                    throw new \RuntimeException('No security entity configured');
                }
                
                if (!$this->securityProvider->isAuthenticated()) {
                    return ResponseCodes::HTTP_UNAUTHORIZED;
                }
                
                if (!$this->securityProvider->isAllowed($roles)) {
                    return ResponseCodes::HTTP_FORBIDDEN;
                }
            }
        }

        return ResponseCodes::HTTP_OK;
    }
}