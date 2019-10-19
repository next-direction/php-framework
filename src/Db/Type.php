<?php

namespace NextDirection\Framework\Db;

use NextDirection\Framework\Common\EnumBase;

abstract class Type extends EnumBase {
    public const STRING   = 'string';
    public const INTEGER  = 'integer';
    public const FLOAT    = 'float';
    public const DECIMAL  = 'decimal';
    public const BOOLEAN  = 'boolean';
    public const DATE     = 'date';
    public const DATETIME = 'datetime';
    
    public const DEFAULTS = [
        'string'   => '',
        'integer'  => 0,
        'float'    => 0.0,
        'decimal'  => 0.0,
        'boolean'  => false,
        'date'     => '',
        'datetime' => '',
    ];
}