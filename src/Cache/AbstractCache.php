<?php

namespace NextDirection\Framework\Cache;

use NextDirection\Framework\Common\AbstractSingleton;
use Psr\SimpleCache\CacheInterface;

abstract class AbstractCache extends AbstractSingleton implements CacheInterface {
    
    /**
     * @var string - reserved characters by PSR-16
     */
    protected const RESERVED = '/\{|\}|\(|\)|\/|\\\\|\@|\:/u';
    
    /**
     * @param string $key
     *
     * @throws InvalidArgumentException
     */
    protected function checkKey(string $key): void {
        
        if (preg_match(self::RESERVED, $key)) {
            throw new InvalidArgumentException('Reserved character used in key');
        }
    }
}