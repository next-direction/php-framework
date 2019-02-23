<?php

namespace NextDirection\Framework\Security\Adapter;

use NextDirection\Framework\Config\Environment;
use NextDirection\Framework\Http\Request;
use NextDirection\Framework\Security\ProviderInterface;

class Jwt implements ProviderInterface {
    
    /**
     * @var Request
     */
    protected $request;
    
    /**
     * @var Environment
     */
    protected $environment;
    
    /**
     * @param Request     $request
     * @param Environment $environment
     */
    public function __construct(Request $request, Environment $environment) {
        $this->request = $request;
        $this->environment = $environment;
    }
    
    /**
     * Check if security entity has one of the required roles
     *
     * @param array $roles - Required roles
     *
     * @return bool
     */
    public function isAllowed(array $roles): bool {
        return true;
    }
    
    /**
     * Check if security entity is authenticated
     *
     * @return bool
     */
    public function isAuthenticated(): bool {
        return true;
    }
}