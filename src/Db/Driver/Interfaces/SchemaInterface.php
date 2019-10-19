<?php

namespace NextDirection\Framework\Db\Driver\Interfaces;

interface SchemaInterface {
    
    /**
     * Check if entity exists in schema
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasEntity(string $name): bool;
    
    /**
     * Create entity in schema
     *
     * @param string $name
     * @param string $from - Rename entity instead of create it
     *
     * @return EntityInterface
     */
    public function createEntity(string $name, string $from = ''): EntityInterface;
    
    /**
     * Return entity for given name
     *
     * @param string $name
     *
     * @return EntityInterface
     */
    public function getEntity(string $name): EntityInterface;
    
    /**
     * Return all entities
     *
     * @return EntityInterface[] - entity name as key
     */
    public function getEntities(): array;
    
    /**
     * Remove entity with given name
     *
     * @param string $name
     */
    public function removeEntity(string $name): void;
}