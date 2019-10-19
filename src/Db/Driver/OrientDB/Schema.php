<?php

namespace NextDirection\Framework\Db\Driver\OrientDB;

use NextDirection\Framework\Config\Reader;
use NextDirection\Framework\Config\Types;
use NextDirection\Framework\Db\Driver\Interfaces\EntityInterface;
use NextDirection\Framework\Db\Driver\Interfaces\SchemaInterface;
use PhpOrient\PhpOrient;
use PhpOrient\Protocols\Binary\Data\Record;

class Schema implements SchemaInterface {
    
    /**
     * @var PhpOrient
     */
    protected $client;
    
    /**
     * @var array
     */
    protected $config;
    
    /**
     * @param PhpOrient $client
     */
    public function __construct(PhpOrient $client) {
        $this->client = $client;
        
        $reader = new Reader(Types::DB);
        $this->config = $reader->get();
    }
    
    /**
     * Check if entity exists in schema
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasEntity(string $name): bool {
        $result = $this->client->query(
            sprintf('SELECT FROM (SELECT EXPAND(classes) FROM metadata:schema) WHERE name = "%s"', $name)
        );
        
        return (bool) count($result);
    }
    
    /**
     * Create entity in schema
     *
     * @param string $name
     * @param string $from
     *
     * @return EntityInterface
     */
    public function createEntity(string $name, string $from = ''): EntityInterface {
        
        if ($from && $this->hasEntity($from)) {
            $this->client->command(
                sprintf('ALTER CLASS %s NAME %s', $from, $name)
            );
    
            $this->client->command(
                sprintf('DELETE VERTEX %s WHERE name = "%s"', $this->config['entityRegistry'], $from)
            );
        } else {
            $this->client->command(
                sprintf('CREATE CLASS %s EXTENDS V', $name)
            );
        }
        
        if ($name !== $this->config['entityRegistry']) {
            $this->client->command(
                sprintf('INSERT INTO %s (name) VALUES ("%s")', $this->config['entityRegistry'], $name)
            );
        }
        
        return new Entity($name, $this->client);
    }
    
    /**
     * Return entity for given name
     *
     * @param string $name
     *
     * @return EntityInterface
     */
    public function getEntity(string $name): EntityInterface {
        return new Entity($name, $this->client);
    }
    
    /**
     * Return all entities
     *
     * @return EntityInterface[] - entity name as key
     */
    public function getEntities(): array {
        $result = $this->client->query('SELECT FROM Entities');
        $entities = [];
        
        /** @var Record $entity */
        foreach ($result as $entity) {
            $entityName = $entity->getOData()['name'];
            $entities[$entityName] = $this->getEntity($entityName);
        }
        
        return $entities;
    }
    
    /**
     * Remove entity with given name
     *
     * @param string $name
     */
    public function removeEntity(string $name): void {
        $this->client->command(
            sprintf('DELETE VERTEX %s', $name)
        );
    
        $this->client->command(
            sprintf('DROP CLASS %s', $name)
        );
    
        $this->client->command(
            sprintf('DELETE VERTEX Entities WHERE name = "%s"', $name)
        );
    }
}