<?php
namespace FS\SolrBundle\Doctrine\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 */
class Type extends Annotation {
	public $type;
	public $name;
	
	private static $TYP_MAPPING = array(
		'string'	=> '_s',
		'text'		=> '_t',
		'date'		=> '_dt',
		'boolean'	=> '_b',
		'integer'	=> '_i'		
	);
	
	public function getNameWithAlias() {
		if ($this->type && array_key_exists($this->type, self::$TYP_MAPPING)) {
			return $this->name. self::$TYP_MAPPING[$this->type];
		}
		
		throw new \RuntimeException('unsupported type'. $this->type);
	}
}
