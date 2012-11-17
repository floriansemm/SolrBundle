<?php
namespace FS\SolrBundle\Tests\Doctrine\Annotation\Entities;

use FS\SolrBundle\Doctrine\Annotation as Solr;

/**
 *
 * @Solr\Document
 * @Solr\SynchronizationFilter(callback="shouldBeIndex")
 */
class ValidTestEntityFiltered {
	private $shouldBeIndexedWasCalled = false;
	
	public function shouldBeIndex() {
		$this->shouldBeIndexedWasCalled = true;
	
		return false;
	}
	
	public function getShouldBeIndexedWasCalled() {
		return $this->shouldBeIndexedWasCalled;
	}
}

?>