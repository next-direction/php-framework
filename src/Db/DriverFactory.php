<?php

namespace NextDirection\Framework\Db;

use NextDirection\Framework\Config\Reader;
use NextDirection\Framework\Config\Types;
use NextDirection\Framework\Db\Driver\Interfaces\DriverInterface;

abstract class DriverFactory {
    
    /**
     * @return DriverInterface
     */
    public static function createInstance(): DriverInterface {
        $config = new Reader(Types::DB);
        $driverClass = $config->get('driver');
        
        /** @var DriverInterface $driverName */
        $driverName = '\NextDirection\Framework\Db\Driver\\' . $driverClass;
        
        if (!class_exists($driverName)) {
            throw new \RuntimeException('Configured database driver not available');
        }
        
        return $driverName::createInstance();
    }
}