<?php
namespace FS\SolrBundle\Tests;
class SolrClientFake {
	private $commit = false;
	
	public function addDocument($doc) {}
	
	public function deleteByQuery($query) {}
	
	public function commit() {
		$this->commit = true;
	}
	
	public function isCommited() {
		return $this->commit;
	}
}
