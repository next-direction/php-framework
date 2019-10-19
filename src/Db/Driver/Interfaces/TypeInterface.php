<?php

namespace NextDirection\Framework\Db\Driver\Interfaces;

interface TypeInterface {
    
    /**
     * Returns type definition for creating a field of this type
     *
     * @param string $type
     *
     * @return string
     *
     * @throws \InvalidArgumentException - If type is not supported
     */
    public static function getFieldDefinition(string $type): string;
}