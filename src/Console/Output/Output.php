<?php

namespace NextDirection\Framework\Console\Output;

abstract class Output {
    
    /**
     * @var int
     */
    private static $lastOutputLength = 0;
    
    /**
     * Output colors
     *
     * @var array
     */
    protected static $colors = [
        OutputType::NORMAL  => 0,
        OutputType::INFO    => 94,
        OutputType::SUCCESS => 92,
        OutputType::WARNING => 93,
        OutputType::ERROR   => 91,
    ];
    
    /**
     * Output styling
     *
     * @var array
     */
    protected static $options = [
        OutputStyle::BOLD => 1,
        OutputStyle::DIMMED => 2,
        OutputStyle::ITALIC => 3,
        OutputStyle::UNDERLINED => 4,
        OutputStyle::STRIKE => 9,
    ];
    
    /**
     * Write a new line to console
     *
     * @param string $message
     * @param int    $type
     * @param array  $styles - Array of OutputStyle values
     */
    public static function writeLine(string $message, int $type = OutputType::NORMAL, array $styles = []): void {
        
        $options = [];
        
        if (OutputType::isValid($type)) {
            $options[] = self::$colors[$type];
        }
    
        foreach ($styles as $option) {
    
            if (OutputStyle::isValid($option)) {
                $options[] = self::$options[$option];
            }
        }
        
        if ($options) {
            sort($options);
            $message = "\033[" . implode(';', $options) . 'm' . $message . "\033[0m";
        }
        
        self::$lastOutputLength = mb_strlen($message);
        
        echo "\n$message";
    }
    
    /**
     * Append text to last output
     *
     * @param string $message
     * @param int    $type
     * @param array  $styles - Array of OutputStyle values
     */
    public static function append(string $message, int $type = OutputType::NORMAL, array $styles = []): void {
    
        $options = [];
    
        if (OutputType::isValid($type)) {
            $options[] = self::$colors[$type];
        }
    
        foreach ($styles as $option) {
        
            if (OutputStyle::isValid($option)) {
                $options[] = self::$options[$option];
            }
        }
    
        if ($options) {
            sort($options);
            $message = "\033[" . implode(';', $options) . 'm' . $message . "\033[0m";
        }
        
        self::$lastOutputLength = self::$lastOutputLength + mb_strlen($message);
        
        echo $message;
    }
    
    /**
     * Replace last output to console
     *
     * @param string $message
     * @param int    $type
     * @param array  $styles - Array of OutputStyle values
     */
    public static function replaceLine(string $message, int $type = OutputType::NORMAL, array $styles = []): void {
    
        $options = [];
    
        if (OutputType::isValid($type)) {
            $options[] = self::$colors[$type];
        }
    
        foreach ($styles as $option) {
        
            if (OutputStyle::isValid($option)) {
                $options[] = self::$options[$option];
            }
        }
    
        if ($options) {
            sort($options);
            $message = "\033[" . implode(';', $options) . 'm' . $message . "\033[0m";
        }
        
        // overwrite previous line
        echo "\r" . str_repeat(' ', self::$lastOutputLength);
    
        self::$lastOutputLength = mb_strlen($message);
        
        // output new line
        echo "\r$message";
    }
}