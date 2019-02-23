<?php

namespace NextDirection\Framework\Console;

use NextDirection\Framework\Common\DirectoryInspection;
use NextDirection\Framework\Config\Reader;
use NextDirection\Framework\Config\Types;
use NextDirection\Framework\Console\Output\Output;
use NextDirection\Framework\Console\Output\OutputStyle;
use NextDirection\Framework\Console\Output\OutputType;

class Console {
    
    /**
     * Used to output formatted command with description
     */
    private const MAX_COMMAND_LENGTH = 40;
    
    /**
     * @param array $arguments
     *
     * @return int
     */
    public function run(array $arguments): int {
        $commands = $this->getAvailableCommands();
        $commandName = isset($arguments[1]) ? $arguments[1] : '';
        
        if (!$commandName || !array_key_exists($commandName, $commands)) {
            $this->showHelp($commands);
            
            return 1;
        }
        
        $command = $commands[$commandName];
        
        return $command->execute();
    }
    
    /**
     * @return AbstractCommand[]
     */
    protected function getAvailableCommands(): array {
        $reader = new Reader(Types::APP);
    
        $frameworkCommandDirectory = __DIR__ . '/../../src/Console/Command';
        $applicationCommandDirectory = $reader->get('commandDirectory');
    
        $frameworkFiles = DirectoryInspection::getFiles($frameworkCommandDirectory);
        $applicationFiles = DirectoryInspection::getFiles($applicationCommandDirectory);
    
        $fullQualifiedCommandNames = DirectoryInspection::getFullQualifiedClassNames(array_merge($frameworkFiles, $applicationFiles));
    
        $commands = [];
        
        foreach ($fullQualifiedCommandNames as $fullQualifiedCommandName) {
        
            try {
                $reflectionClass = new \ReflectionClass($fullQualifiedCommandName);
            
                if ($reflectionClass->isSubclassOf(AbstractCommand::class)) {
                    
                    /** @var AbstractCommand $command */
                    $command = new $fullQualifiedCommandName();
                    $commandName = $command->getName();
                    
                    if ($commandName) {
                        $commands[$commandName] = $command;
                    }
                }
            } catch (\ReflectionException $e) {}
        }
        
        ksort($commands);
        
        return $commands;
    }
    
    /**
     * Displays available commands
     *
     * @param AbstractCommand[] $commands
     */
    protected function showHelp(array $commands): void {
        Output::writeLine('');
        Output::writeLine('No or invalid command specified!', OutputType::WARNING);
        Output::writeLine('');
        Output::writeLine('Available commands:', OutputType::NORMAL, [OutputStyle::BOLD]);
        
        foreach ($commands as $commandName => $command) {
            Output::writeLine('  ' . $commandName, OutputType::INFO);
            Output::append(str_repeat(' ', self::MAX_COMMAND_LENGTH - mb_strlen($commandName)));
            Output::append($command->getDescription(), OutputType::NORMAL, [OutputStyle::ITALIC]);
        }
    }
}