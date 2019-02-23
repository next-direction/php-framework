<?php

namespace NextDirection\Framework\Config;

use NextDirection\Framework\Common\AbstractSingleton;

class Environment extends AbstractSingleton {
    
    /**
     * Path to environment file
     */
    protected const ENV_DIR = __DIR__ . '/../../.env';
    
    /**
     * @var array
     */
    protected $variables = [];
    
    protected function __construct() {

        if (file_exists(self::ENV_DIR)) {
    
            if ($fp = fopen(self::ENV_DIR, 'r')) {
                
                while ($line = fgets($fp)) {
                    
                    if ('' !== trim($line)) {
                        list($name, $value) = explode('=', trim($line));
                        $this->variables[trim($name)] = trim($value, " \t\n\r\0\x0B\"");
                    }
                }
            }
        }
    }
    
    /**
     * @param string $key
     *
     * @return mixed|null - null if not found
     */
    public function get(string $key){
        return isset($this->variables[$key]) ? $this->variables[$key] : null;
    }
}