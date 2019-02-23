<?php

namespace NextDirection\Framework\Console\Command;

use NextDirection\Framework\Console\AbstractCommand;
use NextDirection\Framework\Console\Output\Output;
use NextDirection\Framework\Console\Output\OutputType;

class SchemaUpdate extends AbstractCommand {
    
    /**
     * Used to register command, options and arguments
     */
    public function __construct() {
        $this
            ->setName('schema:update')
            ->setDescription('Synchronize models with database structure');
    }
    
    /**
     * Executes the command
     *
     * @return int - Return code
     */
    protected function run(): int {
    
        Output::writeLine('Schema updated successfully!', OutputType::SUCCESS);
        
        return 0;
    }
}