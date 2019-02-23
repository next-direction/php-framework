<?php

namespace NextDirection\Framework\Security;

interface EntityInterface {
    
    /**
     * Must return the entity roles
     *
     * @return array
     */
    public function getRoles(): array;
}