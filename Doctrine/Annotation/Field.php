<?php
namespace FS\SolrBundle\Doctrine\Annotation;

use Doctrine\Common\Annotations\Annotation;

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
     * @var array
     */
    private static $TYP_MAPPING = array(
        'string' => '_s',
        'strings' => '_ss',
        'text' => '_t',
        'texts' => '_txt',
        'date' => '_dt',
        'dates' => '_dts',
        'boolean' => '_b',
        'booleans' => '_bs',
        'integer' => '_i',
        'integers' => '_is',
        'long' => '_l',
        'longs' => '_ls',
        'float' => '_f',
        'floats' => '_fs',
        'double' => '_d',
        'doubles' => '_ds',
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
        return $this->normalizeName($this->name) . $this->getTypeSuffix($this->type);
    }

    /**
     * @param string $type
     *
     * @return string
     */
    protected function getTypeSuffix($type)
    {
        if ($type == '') {
            return '';
        }

        if (!isset(self::$TYP_MAPPING[$this->type])) {
            return '';
        }

        return self::$TYP_MAPPING[$this->type];
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
     * Check if the current anno has a value getter
     *
     * @return bool
     */
    public function hasGetter()
    {
        return !empty($this->getter);
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
     * @param string $name The field name
     *
     * @return string normalized field name
     */
    protected function normalizeName($name)
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
}
