<?php
declare(strict_types=1);

namespace Willow\Utilities;

use ReflectionClass;
use ReflectionException;

final class DocBlockParser
{
    /**
     * Cache of class reflector objects
     * @var array<ReflectionClass>
     */
    protected $classReflectors = [];

    /**
     * Cache of doc comments for a class
     * @var array<string>
     */
    protected $docComments = [];

    /**
     * Cache of doc comments for a class (in array format)
     * @var array<array<string>>
     */
    protected $docCommentLines = [];

    /**
     * Return a ReflectionClass object for a given class name (retrieving it from cache if possible)
     *
     * @param string $className
     * @return ReflectionClass
     * @throws ReflectionException
     */
    public function getReflectionClass(string $className): ReflectionClass
    {
        if (!key_exists($className, $this->classReflectors)) {
            $this->classReflectors[$className] = new \ReflectionClass($className);
        }
        return $this->classReflectors[$className];
    }

    /**
     * Return the docblock comment for a given class name (retrieving from cache if possible)
     *
     * @param string $className
     * @return string
     * @throws ReflectionException
     */
    public function getDocComment(string $className): string
    {
        if (!key_exists($className, $this->docComments)) {
            $reflector = $this->getReflectionClass($className);
            $this->docComments[$className] = $reflector->getDocComment();
        }
        return $this->docComments[$className];
    }

    /**
     * Returns a docblock comment as an array<string> given a class name (from cache if possible)
     *
     * @param string $className
     * @return array<string>
     * @throws ReflectionException
     */
    public function getDocCommentAsArray(string $className): array
    {
        if (!key_exists($className, $this->docCommentLines)) {
            $docComments = $this->getDocComment($className);
            $this->docCommentLines[$className] = preg_split("/\r\n|\n|\r/", $docComments);
        }
        return $this->docCommentLines[$className];
    }

    /**
     * Returns an associative array of docblock comment lines one for each property
     *
     * @param string $className
     * @return array<string>
     * @throws ReflectionException
     */
    public function getDocProperties(string $className): array
    {
        $docBlock = $this->getDocCommentAsArray($className);
        $properties = [];
        foreach ($docBlock as $line) {
            if (strpos($line, '* @property ') !== false) {
                $propertyName = strBetween('$', ' ', $line);
                if (!empty($propertyName)) {
                    $line = str_replace(' * @property ', '', $line);
                    $line = str_replace(' $' . $propertyName, '', $line);
                    $properties[$propertyName] = $line;
                }
            }
        }
        return $properties;
    }

    public function getDocProperty(string $className, string $propertyName): ?string
    {
        $docBlock = $this->getDocProperties($className);
        return $docBlock[$propertyName] ?? null;
    }

    public function getDocPropertyType(string $className, string $propertyName): ?string
    {
        $propertyLine = $this->getDocProperty($className, $propertyName);
        return strBetween(' * @property ', ' $', $propertyLine);
    }
}

/**
 * @see https://stackoverflow.com/questions/5696412/how-to-get-a-substring-between-two-strings-in-php
 *
 * @param string $start
 * @param string $end
 * @param string $input
 * @return string
 */
function strBetween(string $start, string $end, string $input): string
{
    if (count(explode("$start", $input))>1){
        $content = explode("$end",explode("$start", $input)[1])[0];
    }else{
        $content = "";
    }
    return $content;
}
