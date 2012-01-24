<?php
namespace FS\SolrBundle\Query;

abstract class AbstractQuery {
	
	/**
	 * 
	 * @var \SolrQuery
	 */
	protected $solrQuery = null;
	
	public function __construct() {
		$this->solrQuery = new \SolrQuery('*:*');
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
