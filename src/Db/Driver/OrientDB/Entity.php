<?php

namespace NextDirection\Framework\Db\Driver\OrientDB;

use NextDirection\Framework\Db\Driver\Interfaces\EntityInterface;
use NextDirection\Framework\Db\Driver\Interfaces\FieldInterface;
use PhpOrient\PhpOrient;
use PhpOrient\Protocols\Binary\Data\Record;

class Entity implements EntityInterface {
    
    /**
     * @var string
     */
    protected $name;
    
    /**
     * @var PhpOrient
     */
    protected $client;
    
    /**
     * @param string    $name
     * @param PhpOrient $client
     */
    public function __construct(string $name, PhpOrient $client) {
        $this->name = $name;
        $this->client = $client;
    }
    
    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }
    
    /**
     * Check if field exists on entity
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasField(string $name): bool {
        $result = $this->client->query(
            sprintf('SELECT FROM (
                        SELECT EXPAND(properties) FROM (
                            SELECT EXPAND(classes) FROM metadata:schema
                        ) WHERE name = "%s"
                     ) WHERE name = "%s"', $this->name, $name
            )
        );
        
        return (bool) count($result);
    }
    
    /**
     * Creates a field for entity
     *
     * @param string $name
     * @param string $type
     * @param string $from
     *
     * @return FieldInterface
     */
    public function createField(string $name, string $type, string $from = ''): FieldInterface {
        
        if ($from && $this->hasField($from)) {
            $this->client->command(
                sprintf('ALTER PROPERTY %s.%s NAME %s', $this->name, $from, $name)
            );
        } else {
            $this->client->command(
                sprintf('CREATE PROPERTY %s.%s %s', $this->name, $name, Type::getFieldDefinition($type))
            );
        }
        
        return new Field($name, $this->name, $this->client);
    }
    
    /**
     * Returns field for given name
     *
     * @param string $name
     *
     * @return FieldInterface
     */
    public function getField(string $name): FieldInterface {
        return new Field($name, $this->name, $this->client);
    }
    
    /**
     * Return all fields for this entity
     *
     * @return FieldInterface[]
     */
    public function getFields(): array {
        $result = $this->client->query(
            sprintf('SELECT EXPAND(properties) FROM (
                         SELECT EXPAND(classes) FROM metadata:schema
                     ) WHERE name = "%s"', $this->name
            )
        );
    
        $fields = [];
        
        /** @var Record $property */
        foreach ($result as $property) {
            $name = $property->getOData()['name'];
            $fields[$name] = $this->getField($name);
        }
        
        return $fields;
    }
    
    /**
     * Remove field with given name
     *
     * @param string $name
     */
    public function removeField(string $name): void {
        $this->client->command(
            sprintf('DROP PROPERTY %s.%s FORCE', $this->name, $name)
        );
        $this->client->command(
            sprintf('UPDATE %s REMOVE %s', $this->name, $name)
        );
    }
}