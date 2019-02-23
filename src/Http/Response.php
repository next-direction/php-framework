<?php

namespace NextDirection\Framework\Http;

class Response {
    
    /**
     * @var int - HTTP response code
     */
    protected $code = ResponseCodes::HTTP_OK;
    
    /**
     * @var string - HTTP response message
     */
    protected $message;
    
    /**
     * @var string - Response body
     */
    protected $body = '';
    
    /**
     * @var string
     */
    protected $charset = 'utf-8';
    
    /**
     * @var array - Headers for response
     */
    protected $headers = [
        'Content-Type' => 'text/plain'
    ];
    
    /**
     * @var Request
     */
    protected $request;
    
    /**
     * @param Request $request
     */
    public function __construct(Request $request) {
        $this->message = ResponseCodes::getMessage($this->code);
        $this->request = $request;
    }
    
    /**
     * @param int $code
     *
     * @return Response
     */
    public function setCode(int $code): Response {
        
        if (!ResponseCodes::isValid($code)) {
            throw new \InvalidArgumentException('Invalid status code');
        }
        
        $this->code = $code;
        $this->message = ResponseCodes::getMessage($code);
        
        return $this;
    }
    
    /**
     * @param string $charset
     *
     * @return Response
     */
    public function setCharacterSet(string $charset): Response {
        $this->charset = $charset;
        
        return $this;
    }
    
    /**
     * @param array $data
     *
     * @return Response
     */
    public function setJson(array $data): Response {
        $this->headers['Content-Type'] = 'application/json';
        $this->body = json_encode($data);
        
        return $this;
    }
    
    /**
     * @param string $name
     * @param string $value
     *
     * @return Response
     */
    public function setHeader(string $name, string $value): Response {
        $this->headers[$name] = $value;
    }
    
    /**
     * @param string $body
     *
     * @return Response
     */
    public function setBody(string $body): Response {
        $this->body = $body;
        
        return $this;
    }
    
    /**
     * Redirects to given url
     *
     * @param string $url
     * @param int    $code
     */
    public function redirect(string $url, int $code = ResponseCodes::HTTP_FOUND): void {
        $this->setCode($code);
        $this->setHeader('Location', $url);
        $this->send();
        
        // no further execution desired after redirection
        exit;
    }
    
    /**
     * Checks if response has already been sent
     *
     * @return bool
     */
    public function isSent(): bool {
        return headers_sent();
    }
    
    /**
     * Sends the response to the server
     *
     * @return Response
     */
    public function send(): Response {
        $phpSapiName = mb_substr(php_sapi_name(), 0, 3);
        
        if (in_array($phpSapiName, ['cgi', 'fpm'])) {
            header('Status: ' . $this->code . ' ' . $this->message);
        } else {
            $protocol = $this->request->getProtocol() ? : 'HTTP/1.1';
            header($protocol . ' ' . $this->code . ' ' . $this->message);
        }
        
        foreach ($this->headers as $name => $value) {
            $header = $name . ': ' . $value;
            
            if ('Content-Type' === $name && $this->charset) {
                $header .= '; charset=' . $this->charset;
            }
            
            header($header);
        }
        
        if (!empty($this->body)) {
            echo $this->body;
        }
        
        return $this;
    }
}