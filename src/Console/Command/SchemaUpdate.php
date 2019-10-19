<?php

namespace NextDirection\Framework\Console\Command;

use NextDirection\Framework\Common\DirectoryInspection;
use NextDirection\Framework\Config\Reader;
use NextDirection\Framework\Config\Types;
use NextDirection\Framework\Console\AbstractCommand;
use NextDirection\Framework\Console\Output\Output;
use NextDirection\Framework\Console\Output\OutputType;
use NextDirection\Framework\Db\Driver\Interfaces\EntityInterface;
use NextDirection\Framework\Db\Driver\Interfaces\SchemaInterface;
use NextDirection\Framework\Db\DriverFactory;
use NextDirection\Framework\Db\Type;
use NextDirection\Framework\Mvc\Model;

class SchemaUpdate extends AbstractCommand {
    
    private const RIGHT_OUTPUT_MARGIN = 60;
    
    /**
     * @var SchemaInterface
     */
    protected $schema;
    
    /**
     * Used to register command, options and arguments
     */
    public function __construct() {
        $this
            ->setName('schema:update')
            ->setDescription('Synchronize models with database structure')
            ->addOption('v', 'Print out debug information');
    }
    
    /**
     * Executes the command
     *
     * @return int - Return code
     */
    protected function run(): int {
        
        try {
            $this->initSchema();
            $this->checkRegistry();
            
            $appReader = new Reader(Types::APP);
            $modelDir = $appReader->get('modelDirectory');
            $modelClassNames = DirectoryInspection::getFullQualifiedClassNames($modelDir);
            $entityList = $this->schema->getEntities();
            
            foreach ($modelClassNames as $modelClassName) {
                
                try {
                    $reflectionClass = new \ReflectionClass($modelClassName);
                    $docComment = $reflectionClass->getDocComment();
                    
                    if (preg_match('/@OMEntity/', $docComment) && $reflectionClass->isSubclassOf(Model::class)) {
                        $entityOptions = [];
    
                        if (preg_match('/@OMOptions=(.*)/', $docComment, $entityOptionMatch)) {
                            $entityOptions = json_decode($entityOptionMatch[1], true);
                        }

                        $entity = $this->checkEntity($reflectionClass->getShortName(), $entityOptions);
                        unset($entityList[$entity->getName()]);
                        
                        $properties = $reflectionClass->getProperties();
                        $fields = $entity->getFields();

                        foreach ($properties as $property) {
                            $docComment = $property->getDocComment();
                            
                            if (preg_match('/@OMType=(.*)/', $docComment, $match)) {
                                $options = [];
                                
                                if (preg_match('/@OMOptions=(.*)/', $docComment, $optionMatch)) {
                                    $options = json_decode($optionMatch[1], true);
                                }
    
                                $options['type'] = $match[1];
                                
                                $this->checkField($property->getName(), $entity, $options);
                                unset($fields[$property->getName()]);
                            }
                        }

                        foreach ($fields as $fieldName => $field) {
                            $textLength = 0;
                            
                            if ($this->hasOption('v')) {
                                $text = '  Remove field: ' . $fieldName;
                                $textLength = mb_strlen($text);
                                Output::writeLine($text);
                            }
                            
                            $entity->removeField($fieldName);
    
                            if ($this->hasOption('v')) {
                                Output::append(str_repeat(' ', self::RIGHT_OUTPUT_MARGIN - $textLength) . '[');
                                Output::append('OK', OutputType::SUCCESS);
                                Output::append(']');
                            }
                        }
                    }
                } catch (\ReflectionException $e) {}
                
            }
            
            $this->cleanUpEntities($entityList);
        } catch (\Throwable $t) {
            Output::writeLine('Error during model inspection: ' . $t->getMessage(), OutputType::ERROR);
            
            return 1;
        }
        
        Output::writeLine('Schema updated successfully!', OutputType::SUCCESS);
        
        return 0;
    }
    
    protected function initSchema(): void {
        $driver = DriverFactory::createInstance();
        $this->schema = $driver->getSchema();
    }
    
    protected function checkRegistry(): void {
        $dbReader = new Reader(Types::DB);
        $entityRegistryName = $dbReader->get('entityRegistry');
        $registryEntity = $this->checkEntity($entityRegistryName);
        
        if (!$registryEntity->hasField('name')) {
            $registryEntity->createField('name', Type::STRING);
        }
    }
    
    /**
     * Check if entity exists
     *
     * This function will create the entity in database if it not exists
     *
     * @param string $name
     * @param array  $options
     *
     * @return EntityInterface
     */
    protected function checkEntity(string $name, array $options = []): EntityInterface {
        $textLength = 0;
        
        if ($this->hasOption('v')) {
            $text = 'Checking entity: ' . $name;
            $textLength = mb_strlen($text);
            Output::writeLine($text);
        }
        
        if (!$this->schema->hasEntity($name)) {
            $from = isset($options['renameFrom']) && !empty($options['renameFrom']) ? $options['renameFrom'] : '';
    
            if ($this->hasOption('v')) {
                
                if ($from) {
                    $text = '  Not existing, try to rename ' . $from . '.';
                    $textLength = mb_strlen($text);
                    Output::writeLine($text, OutputType::INFO);
                } else {
                    $text = '  Not existing, will create it.';
                    $textLength = mb_strlen($text);
                    Output::writeLine($text, OutputType::INFO);
                }
            }
            
            $entity = $this->schema->createEntity($name, $from);
        } else {
            $entity = $this->schema->getEntity($name);
        }
    
        if ($this->hasOption('v')) {
            Output::append(str_repeat(' ', self::RIGHT_OUTPUT_MARGIN - $textLength) . '[');
            Output::append('OK', OutputType::SUCCESS);
            Output::append(']');
        }
        
        return $entity;
    }
    
    /**
     * @param string          $name
     * @param EntityInterface $entity
     * @param array           $options
     */
    protected function checkField(string $name, EntityInterface $entity, array $options) {
        $textLength = 0;
    
        if ($this->hasOption('v')) {
            $text = '  Checking field: ' . $name;
            $textLength = mb_strlen($text);
            Output::writeLine($text);
        }
    
        if (!$entity->hasField($name)) {
            $from = isset($options['renameFrom']) && !empty($options['renameFrom']) ? $options['renameFrom'] : '';
        
            if ($this->hasOption('v')) {
            
                if ($from) {
                    $text = '    Not existing, try to rename ' . $from . '.';
                    $textLength = mb_strlen($text);
                    Output::writeLine($text, OutputType::INFO);
                } else {
                    $text = '    Not existing, will create it.';
                    $textLength = mb_strlen($text);
                    Output::writeLine($text, OutputType::INFO);
                }
            }
        
            $field = $entity->createField($name, $options['type'], $from);
        } else {
            $field = $entity->getField($name);
            $type = $field->getType();
            
            if ($type !== $options['type']) {
    
                if ($this->hasOption('v')) {
                    $text = sprintf('    Changing type from "%s" to "%s"', $type, $options['type']);
                    $textLength = mb_strlen($text);
                    Output::writeLine($text, OutputType::INFO);
                }
                
                $field->setType($options['type']);
            }
        }
    
        if ($this->hasOption('v')) {
            Output::append(str_repeat(' ', self::RIGHT_OUTPUT_MARGIN - $textLength) . '[');
            Output::append('OK', OutputType::SUCCESS);
            Output::append(']');
        }
    
        if (isset($options['nullable'])) {
            $field->setNullable($options['nullable']);
        } else {
        
            // default is not nullable
            $field->setNullable(false);
        }

        $field->setDefault(isset($options['default']) ? $options['default'] : Type::DEFAULTS[$options['type']]);
    }
    
    /**
     * @param EntityInterface[] $entityList
     */
    protected function cleanUpEntities(array $entityList) {
    
        foreach ($entityList as $entityName => $entity) {
            $textLength = 0;
            
            if ($this->hasOption('v')) {
                $text = 'Remove entity: ' . $entityName;
                $textLength = mb_strlen($text);
                Output::writeLine($text);
            }
            
            $this->schema->removeEntity($entity->getName());
    
            if ($this->hasOption('v')) {
                Output::append(str_repeat(' ', self::RIGHT_OUTPUT_MARGIN - $textLength) . '[');
                Output::append('OK', OutputType::SUCCESS);
                Output::append(']');
            }
        }
    }
}