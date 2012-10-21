<?php
namespace FS\SolrBundle\Query;

abstract class AbstractQuery {
	
	/**
	 * @var \SolrQuery
	 */
	protected $solrQuery = null;
	
	/**
	 * @var object
	 */
	private $entity = null;	
	
	public function __construct() {
		$this->solrQuery = new \SolrQuery('*:*');
	}
	
	/**
	 * @return the $entity
	 */
	public function getEntity() {
		return $this->entity;
	}
	
	/**
	 * @param object $entity
	 */
	public function setEntity($entity) {
		$this->entity = $entity;
	}	
	
	/**
	 * @return \SolrQuery
	 */
	public function getSolrQuery() {
		$searchTerm = $this->getQueryString();
		if (strlen($searchTerm) > 0) {
			$this->solrQuery->setQuery($searchTerm);
		}
	
		return $this->solrQuery;
	}	
	
	/**
	 * @return string 
	 */
	abstract public function getQueryString();
}
