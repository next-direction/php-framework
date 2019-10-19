<?php

namespace NextDirection\Framework\Console;

use NextDirection\Framework\Console\Output\Output;
use NextDirection\Framework\Console\Output\OutputStyle;
use NextDirection\Framework\Console\Output\OutputType;

/**
 * Class AbstractCommand
 *
 * Base class for all console commands
 *
 * TODO: Add arguments, complete options
 *
 * @package NextDirection\Framework\Console
 */
abstract class AbstractCommand {
    
    /**
     * Used to output formatted option or argument with description
     */
    private const MAX_OPT_ARG_LENGTH = 40;
    
    /**
     * @var string
     */
    private $commandName = '';
    
    /**
     * @var string
     */
    private $description = '';
    
    /**
     * @var array
     */
    private $availableOptions = [];
    
    /**
     * @var array
     */
    private $givenOptions = [];
    
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

    public function showCommandHelp(): void {
        Output::writeLine($this->commandName, OutputType::INFO);
        Output::writeLine('  ' . $this->description);
        
        if (count($this->availableOptions)) {
            Output::writeLine('');
            Output::writeLine('Available options:', OutputType::INFO);
        }
        
        foreach ($this->availableOptions as $optionName => $optionInfo) {
            $option = '  --' . $optionName;
            
            Output::writeLine($option);
            Output::append(str_repeat(' ', self::MAX_OPT_ARG_LENGTH - mb_strlen($option)));
            Output::append($optionInfo['description'], OutputType::NORMAL, [OutputStyle::ITALIC]);
        }
    
        Output::writeLine('');
        Output::writeLine('');
    }
    
    /**
     * Read arguments, options and executes the command
     *
     * @param array $arguments
     *
     * @return int
     */
    public final function execute(array $arguments): int {
        
        if ('' === $this->commandName) {
            Output::writeLine('No command name given', OutputType::ERROR);
            return 1;
        }
        
        foreach ($this->availableOptions as $availableOption => $optionDetails) {
            
            foreach ($arguments as $argument) {
                
                if (
                    0 === mb_strpos($argument, '--' . $availableOption)
                    && (
                        mb_strlen($argument) === mb_strlen('--' . $availableOption)
                        || '=' === $argument[mb_strlen('--' . $availableOption)]
                    )
                ) {
                    
                    if (false !== mb_strpos($argument, '=')) {
                        list(, $value) = explode('=', $argument);
                        $this->givenOptions[$availableOption] = $value;
                    } else {
                        $this->givenOptions[$availableOption] = true;
                    }
                }
            }
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
    
    /**
     * @param string $name
     * @param string $description
     * @param bool   $hasValue
     */
    protected function addOption(string $name, string $description, bool $hasValue = false): void {
        $this->availableOptions[$name] = [
            'description' => $description,
            'hasValue' => $hasValue
        ];
    }
    
    /**
     * @param string $name
     *
     * @return bool
     */
    protected function hasOption(string $name): bool {
        return array_key_exists($name, $this->givenOptions);
    }
    
    /**
     * @param string $name
     *
     * @return string - empty string if not existing
     */
    protected function getOption(string $name): string {
        return array_key_exists($name, $this->givenOptions) ? $this->givenOptions[$name] : '';
    }
}