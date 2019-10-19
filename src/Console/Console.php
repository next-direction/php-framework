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
        
        if (in_array('help', $arguments)) {
            $command->showCommandHelp();
            
            return 0;
        }
        
        $startTime = microtime(true);
        $result = $command->execute($arguments);
        Output::writeLine(sprintf('Command took %.3f seconds', microtime(true) - $startTime), OutputType::INFO);
        
        return $result;
    }
    
    /**
     * @return AbstractCommand[]
     */
    protected function getAvailableCommands(): array {
        $reader = new Reader(Types::APP);
    
        $frameworkCommandDirectory = __DIR__ . '/../../src/Console/Command';
        $applicationCommandDirectory = $reader->get('commandDirectory');
    
        $fullQualifiedCommandNames = array_merge(
            DirectoryInspection::getFullQualifiedClassNames($frameworkCommandDirectory),
            DirectoryInspection::getFullQualifiedClassNames($applicationCommandDirectory)
        );
    
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
        
        Output::writeLine('');
        Output::writeLine('Type "php bin/console.php <command> help" to get help for a specific command!');
        Output::writeLine('');
        Output::writeLine('');
    }
}