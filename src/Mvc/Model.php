<?php

namespace NextDirection\Framework\Mvc;

abstract class Model {
    
    /**
     * As string because different databases can have different formats
     *
     * @var string
     */
    protected $id;
    
    /**
     * @return string
     */
    public function getId(): string {
        return $this->id;
    }
    
    /**
     * @param string $id
     *
     * @return Model
     */
    public function setId(string $id): Model {
        $this->id = $id;
        
        return $this;
    }
}