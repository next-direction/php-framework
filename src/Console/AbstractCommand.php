<?php

namespace NextDirection\Framework\Console;

use NextDirection\Framework\Console\Output\Output;
use NextDirection\Framework\Console\Output\OutputType;

/**
 * Class AbstractCommand
 *
 * Base class for all console commands
 *
 * TODO: Add options and arguments
 *
 * @package NextDirection\Framework\Console
 */
abstract class AbstractCommand {
    
    /**
     * @var string
     */
    private $commandName = '';
    
    /**
     * @var string
     */
    private $description = '';
    
    /**
     * Used to register command, options and arguments
     */
    abstract public function __construct();
    
    /**
     * @return string
     */
    public function getName(): string {
        return $this->commandName;
    }
    
    /**
     * @return string
     */
    public function getDescription(): string {
        return $this->description;
    }
    
    /**
     * Read arguments, options and executes the command
     *
     * @return int
     */
    public function execute(): int {
        
        if ('' === $this->commandName) {
            Output::writeLine('No command name given', OutputType::ERROR);
            return 1;
        }
        
        return $this->run();
    }
    
    /**
     * Executes the command
     *
     * @return int - Return code
     */
    abstract protected function run(): int;
    
    /**
     * @param string $name
     *
     * @return AbstractCommand
     */
    protected function setName(string $name): AbstractCommand {
        $this->commandName = $name;
        
        return $this;
    }
    
    /**
     * @param string $description
     *
     * @return AbstractCommand
     */
    protected function setDescription(string $description): AbstractCommand {
        $this->description = $description;
        
        return $this;
    }
}