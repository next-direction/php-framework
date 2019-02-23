<?php

namespace NextDirection\Framework\Http;

use NextDirection\Framework\Common\AbstractSingleton;

/**
 * Class Request
 *
 * Abstraction of all request variables and headers
 *
 * @package NextDirection\Framework\Http
 */
class Request extends AbstractSingleton {
    
    /**
     * @var string
     */
    protected $url;
    
    /**
     * @var string
     */
    protected $method;
    
    /**
     * @var array
     */
    protected $post;
    
    /**
     * @var string
     */
    protected $queryString;
    
    /**
     * @var array
     */
    protected $query = [];
    
    /**
     * @var bool
     */
    protected $isSecure;
    
    /**
     * @var string
     */
    protected $rawBody;
    
    /**
     * @var array
     */
    protected $cookies;
    
    /**
     * @var array
     */
    protected $headers;
    
    /**
     * @var array
     */
    protected $files;
    
    /**
     * @var string
     */
    protected $protocol;
    
    /**
     * Returns one or all query params
     *
     * @param string $key
     *
     * @return mixed - null if key not found
     */
    public function getQuery(string $key = '') {
        
        if ('' === $key) {
            return $this->query;
        } else {
            return $this->query[$key] ? : null;
        }
    }
    
    public function getQueryString(): string {
        return $this->queryString;
    }
    
    /**
     * Return one or all post variables
     *
     * @param string $key
     *
     * @return mixed - null if key not found
     */
    public function getPost(string $key = '') {
        
        if ('' === $key) {
            return $this->post;
        } else {
            return $this->post[$key] ? : null;
        }
    }
    
    /**
     * @return string
     */
    public function getUrl(): string {
        return $this->url;
    }
    
    /**
     * @return string
     */
    public function getMethod(): string {
        return $this->method;
    }
    
    /**
     * @return bool
     */
    public function isSecure(): bool {
        return $this->isSecure;
    }
    
    /**
     * @return string
     */
    public function getRawBody(): string {
        return $this->rawBody;
    }
    
    /**
     * Return one or all cookies
     *
     * @param string $key
     *
     * @return mixed - null if key not found
     */
    public function getCookie(string $key = '') {
        
        if ('' === $key) {
            return $this->cookies;
        } else {
            return $this->cookies[$key] ? : null;
        }
    }
    
    /**
     * @param string $key
     *
     * @return mixed - null if key not found
     */
    public function getHeader(string $key = '') {
    
        if ('' === $key) {
            return $this->headers;
        } else {
            return $this->headers[$key] ? : null;
        }
    }
    
    /**
     * @return string
     */
    public function getProtocol(): string {
        return $this->protocol;
    }
    
    /**
     * @return array
     */
    public function getFiles(): array {
        return $this->files;
    }
    
    protected function __construct() {
    
        $this->post = $_POST ? : [];
        $this->cookies = $_COOKIE ? : [];
        $this->files = $_FILES ? : [];
    
        $urlParts = explode('?', $_SERVER['REQUEST_URI']);
        $this->url = rtrim($urlParts[0], '/') ? : '/';
        $this->queryString = isset($urlParts[1]) ? $urlParts[1] : '';
        parse_str($this->queryString, $this->query);
        
        $this->isSecure = isset($_SERVER['HTTPS']) && '' !== $_SERVER['HTTPS'] && 'off' !== $_SERVER['HTTPS'];
        $this->protocol = $_SERVER['SERVER_PROTOCOL'];
        $this->method = $_SERVER['REQUEST_METHOD'] ? : '';
        
        $this->rawBody = file_get_contents('php://input');
    
        $this->headers = [];
        
        foreach($_SERVER as $key => $value) {
            
            if ('HTTP_' !== mb_substr($key, 0, 5)) {
                continue;
            }
            
            $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
            $this->headers[$header] = $value;
        }
    }
}