<?php

namespace NextDirection\Framework\Common;

class DirectoryInspection {
    
    /**
     * Return all full qualified class names defined in given file paths
     *
     * @param string $directory
     *
     * @return array
     */
    public static function getFullQualifiedClassNames(string $directory): array {
        $fullQualifiedClassNames = [];
        $controllerFiles = self::getFiles($directory) ? : [];
        
        foreach ($controllerFiles as $file) {
            $tokens = token_get_all(file_get_contents($file));
            $namespace = '';
            $className = '';
            $namespaceStarted = false;
            $classNameStarted = false;
            
            foreach ($tokens as $token) {
                
                if (is_array($token)) {
                    list($tokenType, $tokenValue,) = $token;
                } else {
                    $tokenType = '';
                    $tokenValue = $token;
                }
                
                if (empty(trim($tokenValue))) {
                    continue;
                }
                
                if ($namespaceStarted) {
                    
                    if (';' === $tokenValue) {
                        $namespaceStarted = false;
                    } else {
                        $namespace .= $tokenValue;
                    }
                }
                
                if ($classNameStarted && !empty($tokenValue)) {
                    $className = $tokenValue;
                    $classNameStarted = false;
                }
                
                if (T_NAMESPACE === $tokenType) {
                    $namespaceStarted = true;
                }
                
                if (T_CLASS === $tokenType) {
                    $classNameStarted = true;
                }
                
                if ($namespace && $className) {
                    break;
                }
            }
            
            $fullQualifiedClassNames[] = '\\' . $namespace . '\\' . $className;
        }
        
        return $fullQualifiedClassNames;
    }
    
    /**
     * Return file name and path to all php files in given directory
     *
     * @param string $directory
     *
     * @return array
     */
    protected static function getFiles(string $directory): array {
        $directoryIterator = new \RecursiveDirectoryIterator($directory);
        $iterator = new \RecursiveIteratorIterator($directoryIterator);
        $fileIterator = new \RegexIterator($iterator, '/.+\.php$/i', \RecursiveRegexIterator::MATCH);
        $files = [];
        
        /** @var \SplFileInfo $file */
        foreach ($fileIterator as $file) {
            $files[] = $file->getPathname();
        }
        
        return $files;
    }
}