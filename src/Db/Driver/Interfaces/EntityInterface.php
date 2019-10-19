<?php

namespace NextDirection\Framework\Db\Driver\Interfaces;

interface EntityInterface {
    
    /**
     * @return string
     */
    public function getName(): string;
    
    /**
     * Check if field exists on entity
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasField(string $name): bool;
    
    /**
     * Creates a field for entity
     *
     * @param string $name
     * @param string $type
     * @param string $from
     *
     * @return FieldInterface
     */
    public function createField(string $name, string $type, string $from = ''): FieldInterface;
    
    /**
     * Returns field for given name
     *
     * @param string $name
     *
     * @return FieldInterface
     */
    public function getField(string $name): FieldInterface;
    
    /**
     * Return all fields for this entity
     *
     * @return FieldInterface[]
     */
    public function getFields(): array;
    
    /**
     * Remove field with given name
     *
     * @param string $name
     */
    public function removeField(string $name): void;
}