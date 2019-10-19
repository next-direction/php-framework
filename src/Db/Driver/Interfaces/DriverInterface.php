<?php

namespace NextDirection\Framework\Db\Driver\Interfaces;

interface DriverInterface {
    
    /**
     * @return DriverInterface
     */
    public static function createInstance(): DriverInterface;
    
    /**
     * @return SchemaInterface
     */
    public function getSchema(): SchemaInterface;
}