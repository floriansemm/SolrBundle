<?php
namespace FS\SolrBundle\Doctrine\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 */
class Field extends Annotation {
	
	/**
	 * @var string
	 */
	public $type;
	
	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var numeric
	 */
	public $boost = 0;
	
	/**
	 * @var array
	 */
	private static $TYP_MAPPING = array(
		'string'	=> '_s',
		'text'		=> '_t',
		'date'		=> '_dt',
		'boolean'	=> '_b',
		'integer'	=> '_i'		
	);
	
	/**
	 * returns field name with type-suffix:
	 * 
	 * eg: title_s
	 * 
	 * @throws \RuntimeException
	 * @return string
	 */
	public function getNameWithAlias() {
		if ($this->type && array_key_exists($this->type, self::$TYP_MAPPING)) {
			return $this->name. self::$TYP_MAPPING[$this->type];
		}
		
		throw new \RuntimeException('unsupported type'. $this->type);
	}
	
	/**
	 * @return string
	 */
	public function getValue() {
		return $this->value;
	}
	
	/**
	 * @return string
	 */
	public function __toString() {
		return $this->name;
	}
	
	/**
	 * @throws \InvalidArgumentException if boost is not a number
	 * @return number
	 */
	public function getBoost() {
		if (!is_numeric($this->boost)) {
			throw new \InvalidArgumentException(sprintf('Invalid boost value %s', $this->boost));
		}
		return floatval($this->boost);
	}	
}
