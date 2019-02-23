<?php

namespace NextDirection\Framework\Common;

abstract class AbstractSingleton {
    
    /**
     * @var mixed[]
     */
    protected static $instances = [];
    
    /**
     * @return mixed
     */
    public static function createInstance() {
        $className = get_called_class();
        
        if (!isset(self::$instances[$className]) || null === self::$instances[$className]) {
            self::$instances[$className] = new static();
        }
        
        return static::$instances[$className];
    }
    
    abstract protected function __construct();
}