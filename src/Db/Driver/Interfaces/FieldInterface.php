<?php

namespace NextDirection\Framework\Db\Driver\Interfaces;

interface FieldInterface {
    
    /**
     * @return bool
     */
    public function isNullable(): bool;
    
    /**
     * @param bool $nullable
     *
     * @return FieldInterface
     */
    public function setNullable(bool $nullable): FieldInterface;
    
    /**
     * @return mixed
     */
    public function getDefault();
    
    /**
     * @param mixed $default
     *
     * @return FieldInterface
     */
    public function setDefault($default): FieldInterface;
    
    /**
     * @return string
     */
    public function getType(): string;
    
    /**
     * @param string $type
     *
     * @return FieldInterface
     */
    public function setType(string $type): FieldInterface;
}