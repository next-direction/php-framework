<?php

namespace NextDirection\Framework\Db\Driver;

use NextDirection\Framework\Config\Reader;
use NextDirection\Framework\Config\Types;
use NextDirection\Framework\Db\Driver\Interfaces\DriverInterface;
use NextDirection\Framework\Db\Driver\OrientDB\Schema;
use NextDirection\Framework\Db\Driver\Interfaces\SchemaInterface;
use PhpOrient\PhpOrient;

class OrientDB implements DriverInterface {
    
    /**
     * @var DriverInterface
     */
    protected static $instance;
    
    /**
     * @var PhpOrient
     */
    protected $client;
    
    /**
     * @return SchemaInterface
     */
    public function getSchema(): SchemaInterface {
        return new Schema($this->client);
    }
    
    /**
     * @return DriverInterface
     */
    public static function createInstance(): DriverInterface {
        
        if (null === self::$instance) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    protected function __construct() {
        $dbConfig = new Reader(Types::DB);
        $driverConfig = $dbConfig->get('config');
        
        $this->client = new PhpOrient();
        $this->client->configure([
            'username' => $driverConfig['username'],
            'password' => $driverConfig['password'],
            'hostname' => $driverConfig['hostname'],
            'port'     => $driverConfig['port'],
        ]);
        $this->client->connect();
        
        if (!$this->client->dbExists($driverConfig['dbname'], PhpOrient::DATABASE_TYPE_GRAPH)) {
            throw new \RuntimeException('Make sure configured database exists and type is "graph"');
        }
    
        $this->client->dbOpen($driverConfig['dbname'], $driverConfig['dbuser'], $driverConfig['dbpassword']);
    }
}