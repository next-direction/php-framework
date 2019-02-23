<?php

namespace NextDirection\Framework\Cache\Adapter;

use NextDirection\Framework\Cache\AbstractCache;
use NextDirection\Framework\Config\Reader;
use NextDirection\Framework\Config\Types;
use Psr\SimpleCache\CacheInterface;

class File extends AbstractCache implements CacheInterface {
    
    /**
     * @var string
     */
    protected $basePath;
    
    /**
     * @var int
     */
    protected $ttl;
    
    /**
     * Fetches a value from the cache.
     *
     * @param string $key     The unique key of this item in the cache.
     * @param mixed  $default Default value to return if the key does not exist.
     *
     * @return mixed The value of the item from the cache, or $default in case of cache miss.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function get($key, $default = null) {
        $this->checkKey($key);
        
        $path = $this->getPath($key);
        
        if (!file_exists($path)) {
            return $default;
        }
        
        $expires = filemtime($path);
        
        if (time() >= $expires) {
            unlink($path);
            return $default;
        }
        
        $serializedValue = file_get_contents($path);
    
        // otherwise not distinguishable from error during unserialize
        if ('b:0;' === $serializedValue) {
            return false;
        }
        
        $value = unserialize($serializedValue);
        
        if (false === $value) {
            return $default;
        }
        
        return $value;
    }
    
    /**
     * Persists data in the cache, uniquely referenced by a key with an optional expiration TTL time.
     *
     * @param string                 $key   The key of the item to store.
     * @param mixed                  $value The value of the item to store, must be serializable.
     * @param null|int|\DateInterval $ttl   Optional. The TTL value of this item. If no value is sent and
     *                                      the driver supports TTL then the library may set a default value
     *                                      for it or let the driver take care of that.
     *
     * @return bool True on success and false on failure.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function set($key, $value, $ttl = null) {
        $this->checkKey($key);

        if (is_int($ttl)) {
            $expires = time() + $ttl;
        } elseif ($ttl instanceof \DateInterval) {
            $expires = date_create_from_format('U', time())->add($ttl)->getTimestamp();
        } elseif ($ttl === null) {
            $expires = time() + $this->ttl;
        } else {
            throw new InvalidArgumentException('Invalid TTL given');
        }

        $path = $this->getPath($key);
        
        if (!$path) {
            return false;
        }

        if (!touch($path, $expires)) {
            return false;
        }
        
        if (!file_put_contents($path, serialize($value))) {
            return false;
        }
    
        // set time again after putting in content
        if (!touch($path, $expires)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Delete an item from the cache by its unique key.
     *
     * @param string $key The unique cache key of the item to delete.
     *
     * @return bool True if the item was successfully removed. False if there was an error.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function delete($key) {
        $this->checkKey($key);
        
        $path = $this->getPath($key);
        
        if (file_exists($path)) {
            return unlink($path);
        }
        
        return true;
    }
    
    /**
     * Wipes clean the entire cache's keys.
     *
     * @return bool True on success and false on failure.
     */
    public function clear() {
        $directoryIterator = new \RecursiveDirectoryIterator(
            $this->basePath,
            \FilesystemIterator::CURRENT_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS
        );
        
        $iterator = new \RecursiveIteratorIterator($directoryIterator);
        
        foreach ($iterator as $path) {
            
            if (is_dir($path)) {
                continue;
            }
            
            unlink($path);
        }
        
        return true;
    }
    
    /**
     * Obtains multiple cache items by their unique keys.
     *
     * @param iterable $keys    A list of keys that can obtained in a single operation.
     * @param mixed    $default Default value to return for keys that do not exist.
     *
     * @return iterable A list of key => value pairs. Cache keys that do not exist or are stale will have $default as value.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getMultiple($keys, $default = null) {
        
        if (!is_array($keys) && !$keys instanceof \Traversable) {
            throw new InvalidArgumentException('Keys must be an array or an instance of Traversable');
        }
        
        $values = [];
        
        foreach ($keys as $key) {
            $value = $this->get($key);
            $values[$key] = null !== $value ? $value : $default;
        }
        
        return $values;
    }
    
    /**
     * Persists a set of key => value pairs in the cache, with an optional TTL.
     *
     * @param iterable               $values A list of key => value pairs for a multiple-set operation.
     * @param null|int|\DateInterval $ttl    Optional. The TTL value of this item. If no value is sent and
     *                                       the driver supports TTL then the library may set a default value
     *                                       for it or let the driver take care of that.
     *
     * @return bool True on success and false on failure.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if $values is neither an array nor a Traversable,
     *   or if any of the $values are not a legal value.
     */
    public function setMultiple($values, $ttl = null) {
        
        if (!is_array($values) && !$values instanceof \Traversable) {
            throw new InvalidArgumentException('Values must be an array or an instance of Traversable');
        }
    
        $success = true;
        
        foreach ($values as $key => $value) {
            $success = $this->set($key, $value, $ttl) && $success;
        }
        
        return $success;
    }
    
    /**
     * Deletes multiple cache items in a single operation.
     *
     * @param iterable $keys A list of string-based keys to be deleted.
     *
     * @return bool True if the items were successfully removed. False if there was an error.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if $keys is neither an array nor a Traversable,
     *   or if any of the $keys are not a legal value.
     */
    public function deleteMultiple($keys) {
        
        if (!is_array($keys) && !$keys instanceof \Traversable) {
            throw new InvalidArgumentException('Keys must be an array or an instance of Traversable');
        }
        
        $success = true;
        
        foreach ($keys as $key) {
            $success = $this->delete($key) && $success;
        }
        
        return $success;
    }
    
    /**
     * Determines whether an item is present in the cache.
     *
     * NOTE: It is recommended that has() is only to be used for cache warming type purposes
     * and not to be used within your live applications operations for get/set, as this method
     * is subject to a race condition where your has() will return true and immediately after,
     * another script can remove it making the state of your app out of date.
     *
     * @param string $key The cache item key.
     *
     * @return bool
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function has($key) {
        $this->checkKey($key);
        
        return $this->get($key, $this) !== $this;
    }
    
    protected function __construct() {
        $reader = new Reader(Types::FILE_CACHE);
        
        $this->basePath = $reader->get('cacheDir');
        $this->ttl = $reader->get('ttl');
    }
    
    /**
     * Returns path for given key
     *
     * @param string $key
     *
     * @return string
     *
     * @throws InvalidArgumentException if the specified key contains an invalid character
     */
    protected function getPath($key) {
        $this->checkKey($key);
        
        $hash = hash('sha256', $key);
        $path = $this->basePath . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, [$hash[0], $hash[1], mb_substr($hash, 2)]);

        if (!is_dir(dirname($path))) {
            
            if (!mkdir(dirname($path), 0777, true)) {
                return '';
            }
        }
        
        return $path;
    }
}