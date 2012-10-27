<?php
namespace FS\SolrBundle\Doctrine\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 */
class Document extends Annotation {
	public $repository = '';
	public $boost = 0;
	
	public function getBoost() {
		return $this->boost;
	}
}

?>