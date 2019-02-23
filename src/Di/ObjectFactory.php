<?php

namespace NextDirection\Framework\Di;

use NextDirection\Framework\Config\Reader;
use NextDirection\Framework\Config\Types;

class ObjectFactory {
    
    /**
     * Creates an object of given class and injects dependencies
     *
     * @param string $fullQualifiedName
     *
     * @return mixed - Instance of given class with injected dependencies
     */
    public static function createInstance(string $fullQualifiedName) {

        try {
            $reflectionClass = new \ReflectionClass($fullQualifiedName);

            if (
                $reflectionClass->hasMethod('createInstance')
                && ($creationMethod = $reflectionClass->getMethod('createInstance'))->isStatic()
            ) {
                $parameters = self::createMethodParameters($creationMethod);

                return $fullQualifiedName::createInstance(...$parameters);
            } else if ($constructor = $reflectionClass->getConstructor()) {
                $parameters = self::createMethodParameters($constructor);
                
                return new $fullQualifiedName(...$parameters);
            } else {
                
                // no explicit constructor
                return new $fullQualifiedName();
            }
        } catch (\ReflectionException $e) {
            // as mentioned in docs, this would only be thrown on write access to $reflectionClass->name
        }
        
        return null;
    }
    
    /**
     * Create list of parameters required for method call
     *
     * @param \ReflectionMethod $method
     *
     * @return array
     */
    protected static function createMethodParameters(\ReflectionMethod $method): array {
        $reflectionParameters = $method->getParameters();
        $methodParameters = [];
        
        foreach ($reflectionParameters as $reflectionParameter) {

            if ($class = $reflectionParameter->getClass()) {

                if ($class->isInterface()) {
                    $reader = new Reader(Types::DI);
                    $className = $reader->get($class->getName());
                    
                    if ($className) {
                        $methodParameters[] = self::createInstance($className);
                    } else {
                        throw new \RuntimeException('No class found for injection!');
                    }
                } else {
                    $methodParameters[] = self::createInstance($class->getName());
                }
            }
        }
        
        return $methodParameters;
    }
}