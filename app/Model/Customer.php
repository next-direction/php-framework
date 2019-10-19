<?php

namespace NextDirection\Application\Model;

use NextDirection\Framework\Mvc\Model;

class Customer extends Model {
    
    /**
     * @var string
     * @OMType=string
     */
    protected $firstName;
    
    /**
     * @var string
     * @OMType=string
     */
    protected $lastName;
    
    /**
     * @var Address[]
     */
    protected $addresses;
    
    /**
     * @return string
     */
    public function getFirstName(): string {
        return $this->firstName;
    }
    
    /**
     * @param string $firstName
     *
     * @return Customer
     */
    public function setFirstName(string $firstName): Customer {
        $this->firstName = $firstName;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getLastName(): string {
        return $this->lastName;
    }
    
    /**
     * @param string $lastName
     *
     * @return Customer
     */
    public function setLastName(string $lastName): Customer {
        $this->lastName = $lastName;
        
        return $this;
    }
    
    /**
     * @return Address[]
     */
    public function getAddresses(): array {
        return $this->addresses;
    }
    
    /**
     * @param Address[] $addresses
     *
     * @return Customer
     */
    public function setAddresses(array $addresses): Customer {
        $this->addresses = $addresses;
        
        return $this;
    }
}