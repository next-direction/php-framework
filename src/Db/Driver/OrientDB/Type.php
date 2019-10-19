<?php

namespace NextDirection\Framework\Db\Driver\OrientDB;

use NextDirection\Framework\Db\Driver\Interfaces\TypeInterface;

class Type implements TypeInterface {
    
    /**
     * @var string[]
     */
    protected static $typeDefinitions = [
        'string'   => 'String',
        'integer'  => 'Integer',
        'float'    => 'Float',
        'decimal'  => 'Decimal',
        'boolean'  => 'Boolean',
        'date'     => 'Date',
        'datetime' => 'Datetime'
    ];
    
    /**
     * Returns type definition for creating a field of this type
     *
     * @param string $type
     *
     * @return string
     *
     * @throws \InvalidArgumentException - If type is not supported
     */
    public static function getFieldDefinition(string $type): string {
        
        if (!array_key_exists($type, self::$typeDefinitions)) {
            throw new \InvalidArgumentException('Type not supported');
        }
        
        return self::$typeDefinitions[$type];
    }
}