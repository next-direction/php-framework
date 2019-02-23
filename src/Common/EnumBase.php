<?php

namespace NextDirection\Framework\Common;

abstract class EnumBase {
    
    /**
     * Checks if given value is valid enum value
     *
     * @param mixed $value
     *
     * @return bool
     */
    public static function isValid($value) {
        $constants = self::getConstants();

        return in_array($value, array_values($constants), true);
    }
    
    /**
     * Get available constants and values
     *
     * @return array
     */
    protected static function getConstants(): array {
        
        try {
            $reflectionClass = new \ReflectionClass(static::class);
            $constants = $reflectionClass->getConstants();
        } catch (\ReflectionException $e) {
            $constants = [];
        }
        
        return $constants;
    }
}