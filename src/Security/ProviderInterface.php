<?php

namespace NextDirection\Framework\Security;

interface ProviderInterface {
    
    /**
     * Check if security entity is authenticated
     *
     * @return bool
     */
    public function isAuthenticated(): bool;
    
    /**
     * Check if security entity has one of the required roles
     *
     * @param array $roles - Required roles
     *
     * @return bool
     */
    public function isAllowed(array $roles): bool;
}