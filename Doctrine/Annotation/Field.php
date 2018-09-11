<?php
namespace FS\SolrBundle\Doctrine\Annotation;

use Doctrine\Common\Annotations\Annotation;
use phpDocumentor\Reflection\DocBlock\Type\Collection;

/**
 * Defines a field of a solr-document
 *
 * @Annotation
 */
class Field extends Annotation
{

    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $name;

    /**
     * @var float
     */
    public $boost = 0;

    /**
     * @var string
     */
    public $getter;

    /**
     * @var string
     */
    public $fieldsGetter;
    
    /**
     * @var string
     */
    public $fieldModifier;

    /**
     * @var string|bool
     */
    public $fieldAlias = false;
    
    /**
     * @var array
     */
    private static $TYP_MAPPING = array();

    /**
     * @var array
     */
    private static $TYP_SIMPLE_MAPPING = array(
        'string' => '_s',
        'text' => '_t',
        'date' => '_dt',
        'boolean' => '_b',
        'integer' => '_i',
        'long' => '_l',
        'float' => '_f',
        'double' => '_d',
        'datetime' => '_dt'
    );

    /**
     * @var array
     */
    private static $TYP_COMPLEX_MAPPING = array(
        'doubles' => '_ds',
        'floats' => '_fs',
        'longs' => '_ls',
        'integers' => '_is',
        'booleans' => '_bs',
        'dates' => '_dts',
        'texts' => '_txt',
        'strings' => '_ss',
    );

    /**
     * returns field name with type-suffix:
     *
     * eg: title_s
     *
     * @throws \RuntimeException
     *
     * @return string
     */
    public function getNameWithAlias()
    {
        if($this->fieldAlias) {
            return $this->normalizeName($this->fieldAlias) . $this->getTypeSuffix($this->type);
        }
        
        return $this->normalizeName($this->name) . $this->getTypeSuffix($this->type);
    }

    /**
     * @param string $type
     *
     * @return string
     */
    private function getTypeSuffix($type)
    {
        self::$TYP_MAPPING = array_merge(self::$TYP_COMPLEX_MAPPING, self::$TYP_SIMPLE_MAPPING);

        if ($type == '') {
            return '';
        }

        if (!isset(self::$TYP_MAPPING[$this->type])) {
            return '';
        }

        return self::$TYP_MAPPING[$this->type];
    }

     /**
     * Remove parenthesis
     * 
     * @param $methodName
     * @return string
     */
    static public function removeParenthesis($methodName) {
        
        if (strpos($methodName, '(') !== false) {
            $methodName = substr($methodName, 0, strpos($methodName, '('));
        }
        
        return $methodName;
    } 
    
    /**
     * Related object getter name
     *
     * @return string
     */
    public function getGetterName()
    {
        return $this->getter;
    }

    /**
     * Get fields getter
     *
     * @return string
     */
    public function getFieldsGetter()
    {
        return $this->fieldsGetter;    
    }
    
    /**
     * @return string
     */
    public function getFieldModifier()
    {
        return $this->fieldModifier;
    }

    /**
     * @return string
     */
    public function getFieldAlias()
    {
        return $this->fieldAlias;
    }
    
    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * @throws \InvalidArgumentException if boost is not a number
     *
     * @return number
     */
    public function getBoost()
    {
        if (!is_numeric($this->boost)) {
            throw new \InvalidArgumentException(sprintf('Invalid boost value %s', $this->boost));
        }

        if (($boost = floatval($this->boost)) > 0) {
            return $boost;
        }

        return null;
    }

    /**
     * normalize class attributes camelcased names to underscores
     * (according to solr specification, document field names should
     * contain only lowercase characters and underscores to maintain
     * retro compatibility with old components).
     *
     * @param $name The field name
     *
     * @return string normalized field name
     */
    public function normalizeName($name)
    {
        $words = preg_split('/(?=[A-Z])/', $name);
        $words = array_map(
            function ($value) {
                return strtolower($value);
            },
            $words
        );

        return implode('_', $words);
    }

    /**
     * @return array
     */
    public static function getComplexFieldMapping()
    {
        return self::$TYP_COMPLEX_MAPPING;
    }
}
