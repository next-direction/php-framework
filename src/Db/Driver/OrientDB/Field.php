<?php

namespace NextDirection\Framework\Db\Driver\OrientDB;

use NextDirection\Framework\Db\Driver\Interfaces\FieldInterface;
use PhpOrient\PhpOrient;
use PhpOrient\Protocols\Binary\Data\Record;

class Field implements FieldInterface {
    
    /**
     * @var string
     */
    protected $name;
    
    /**
     * @var string
     */
    protected $entityName;
    
    /**
     * @var PhpOrient
     */
    protected $client;
    
    /**
     * @var array
     */
    protected $typeMapping = [
        'string'   => 7,
        'integer'  => 1,
        'float'    => 4,
        'decimal'  => 21,
        'boolean'  => 0,
        'date'     => 19,
        'datetime' => 6
    ];
    
    /**
     * @param string    $name
     * @param string    $entityName
     * @param PhpOrient $client
     */
    public function __construct(string $name, string $entityName, PhpOrient $client) {
        $this->name = $name;
        $this->entityName = $entityName;
        $this->client = $client;
    }
    
    /**
     * @return bool
     */
    public function isNullable(): bool {
        
        /** @var Record $meta */
        $meta = $this->client->query(
            sprintf('SELECT FROM (
                        SELECT EXPAND(properties) FROM (
                            SELECT EXPAND(classes) FROM metadata:schema
                        ) WHERE name = "%s"
                     ) WHERE name = "%s"', $this->entityName, $this->name
            )
        )[0];
        
        return !(bool) $meta->getOData()['notNull'];
    }
    
    /**
     * @param bool $nullable
     *
     * @return FieldInterface
     */
    public function setNullable(bool $nullable): FieldInterface {
        $this->client->command(
            sprintf('ALTER PROPERTY %s.%s NOTNULL %s', $this->entityName, $this->name, $nullable ? 'FALSE' : 'TRUE')
        );
        
        return $this;
    }
    
    /**
     * @return mixed
     */
    public function getDefault() {
        /** @var Record $meta */
        $meta = $this->client->query(
            sprintf('SELECT FROM (
                        SELECT EXPAND(properties) FROM (
                            SELECT EXPAND(classes) FROM metadata:schema
                        ) WHERE name = "%s"
                     ) WHERE name = "%s"', $this->entityName, $this->name
            )
        )[0];
    
        return $meta->getOData()['defaultValue'];
    }
    
    /**
     * @param mixed $default
     *
     * @return FieldInterface
     */
    public function setDefault($default): FieldInterface {
        
        if (is_int($default)) {
            $command = 'ALTER PROPERTY %s.%s DEFAULT %d';
        } else if (is_bool($default)) {
            $command = 'ALTER PROPERTY %s.%s DEFAULT %s';
            $default = $default ? 'TRUE' : 'FALSE';
        } else if (is_float($default)) {
            $command = 'ALTER PROPERTY %s.%s DEFAULT %f';
        } else {
            $command = 'ALTER PROPERTY %s.%s DEFAULT "%s"';
        }
    
        $this->client->command(
            sprintf($command, $this->entityName, $this->name, $default)
        );
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getType(): string {
        
        /** @var Record $meta */
        $meta = $this->client->query(
            sprintf('SELECT FROM (
                        SELECT EXPAND(properties) FROM (
                            SELECT EXPAND(classes) FROM metadata:schema
                        ) WHERE name = "%s"
                     ) WHERE name = "%s"', $this->entityName, $this->name
            )
        )[0];
        
        $typeNumber = $meta->getOData()['type'];
    
        return array_search($typeNumber, $this->typeMapping);
    }
    
    /**
     * @param string $type
     *
     * @return FieldInterface
     */
    public function setType(string $type): FieldInterface {
        $this->client->command(
            sprintf('ALTER PROPERTY %s.%s TYPE "%s"', $this->entityName, $this->name, Type::getFieldDefinition($type))
        );
        
        return $this;
    }
}