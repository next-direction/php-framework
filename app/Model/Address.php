<?php

namespace NextDirection\Application\Model;

use NextDirection\Framework\Mvc\Model;

class Address extends Model {
    
    /**
     * @var string
     * @OMType=string
     */
    protected $street;
    
    /**
     * @var string
     * @OMType=string
     */
    protected $city;
    
    /**
     * @var int
     * @OMType=integer
     */
    protected $zip;
    
    /**
     * @var string
     * @OMType=string
     */
    protected $country;
    
    /**
     * @var Customer
     */
    protected $customer;
    
    /**
     * @return string
     */
    public function getStreet(): string {
        return $this->street;
    }
    
    /**
     * @param string $street
     *
     * @return Address
     */
    public function setStreet(string $street): Address {
        $this->street = $street;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getCity(): string {
        return $this->city;
    }
    
    /**
     * @param string $city
     *
     * @return Address
     */
    public function setCity(string $city): Address {
        $this->city = $city;
        
        return $this;
    }
    
    /**
     * @return int
     */
    public function getZip(): int {
        return $this->zip;
    }
    
    /**
     * @param int $zip
     *
     * @return Address
     */
    public function setZip(int $zip): Address {
        $this->zip = $zip;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getCountry(): string {
        return $this->country;
    }
    
    /**
     * @param string $country
     *
     * @return Address
     */
    public function setCountry(string $country): Address {
        $this->country = $country;
        
        return $this;
    }
    
    /**
     * @return Customer
     */
    public function getCustomer(): Customer {
        return $this->customer;
    }
    
    /**
     * @param Customer $customer
     *
     * @return Address
     */
    public function setCustomer(Customer $customer): Address {
        $this->customer = $customer;
        
        return $this;
    }
}