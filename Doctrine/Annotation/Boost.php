<?php
namespace FS\SolrBundle\Doctrine\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 */
class Boost extends Annotation {
	public function getBoost() {
		return $this->value;
	}
}

?>