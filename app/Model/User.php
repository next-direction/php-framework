<?php

namespace NextDirection\Application\Model;

use NextDirection\Framework\Mvc\Model;
use NextDirection\Framework\Security\EntityInterface;

/**
 * Class User
 *
 * @OMEntity
 * @OMOptions={"renameFrom":"Users"}
 *
 * @package NextDirection\Application\Model
 */
class User extends Model implements EntityInterface {
    
    /**
     * @var string
     * @OMType=string
     * @OMOptions={"nullable":true}
     */
    protected $firstName;
    
    /**
     * @var string
     * @OMType=string
     * @OMOptions={"nullable":true}
     */
    protected $lastName;
    
    /**
     * @var string
     * @OMType=string
     */
    protected $userName;
    
    /**
     * @var string
     * @OMType=string
     * @OMOptions={"renameFrom":"emailAddress","default":"test@localhost.domain"}
     */
    protected $email;
    
    /**
     * @var int
     * @OMType=integer
     * @OMOptions={"default":0}
     */
    protected $age;
    
    /**
     * @var bool
     * @OMType=boolean
     * @OMOptions={"default":true}
     */
    protected $hasProperty;
    
    /**
     * @var \DateTime
     * @OMType=date
     * @OMOptions={"default":"1970-01-01"}
     */
    protected $someDate;
    
    /**
     * @var \DateTime
     * @OMType=datetime
     * @OMOptions={"default":"1970-01-01 00:00:00"}
     */
    protected $someDateTime;
    
    /**
     * Must return the entity roles
     *
     * @return array
     */
    public function getRoles(): array {
        // TODO: Implement getRoles() method.
    }
    
    /**
     * @return string
     */
    public function getFirstName(): string {
        return $this->firstName;
    }
    
    /**
     * @param string $firstName
     *
     * @return User
     */
    public function setFirstName(string $firstName): User {
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
     * @return User
     */
    public function setLastName(string $lastName): User {
        $this->lastName = $lastName;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getUserName(): string {
        return $this->userName;
    }
    
    /**
     * @param string $userName
     *
     * @return User
     */
    public function setUserName(string $userName): User {
        $this->userName = $userName;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getEmail(): string {
        return $this->email;
    }
    
    /**
     * @param string $email
     *
     * @return User
     */
    public function setEmail(string $email): User {
        $this->email = $email;
        
        return $this;
    }
}