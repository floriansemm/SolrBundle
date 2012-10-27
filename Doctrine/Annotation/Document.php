<?php
namespace FS\SolrBundle\Doctrine\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 */
class Document extends Annotation {
	public $repository = '';
	public $boost = 0;
	
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

?>