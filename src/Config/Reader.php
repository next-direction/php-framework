<?php

namespace NextDirection\Framework\Config;

class Reader {
    
    /**
     * @var array
     */
    protected $values;
    
    /**
     * @param string $configType - Value of \NextDirection\Framework\Config\Types
     */
    public function __construct(string $configType) {
        
        if (Types::isValid($configType)) {
            
            if (file_exists($configType)) {
                $this->values = require($configType);
            } else {
                $this->values = [];
            }
        }
    }
    
    /**
     * @param string $key - If empty, all values of config will be returned
     *
     * @return mixed
     */
    public function get(string $key = '') {

        if ('' !== $key) {
            return $this->values[$key];
        } else {
            return $this->values;
        }
    }
}