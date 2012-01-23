<?php
namespace FS\SolrBundle;

use FS\SolrBundle\Query\AbstractQuery;

class SolrQuery extends AbstractQuery {
	private $mappedFields = array();
	
	/**
	 * 
	 * @var \SolrQuery
	 */
	private $solrQuery = null;
	
	private $searchTerms = array();
	
	private $strict = false;
	
	/**
	 * 
	 * @var object
	 */
	private $entity = null;
	
	public function __construct() {
		$this->solrQuery = new \SolrQuery('*:*');
	}
	
	/**
	 * @return array
	 */
	public function getMappedFields() {
		return $this->mappedFields;
	}

	/**
	 * @param array $mappedFields
	 */
	public function setMappedFields($mappedFields) {
		$this->mappedFields = $mappedFields;
	}
	
	public function setStrict($strict) {
		$this->strict = $strict;
	}
	
	/**
	 * @return array
	 */
	public function getSearchTerms() {
		return $this->searchTerms;
	}

	public function queryAllFields($value) {
		$this->setStrict(false);
		
		foreach ($this->mappedFields as $documentField => $entityField) {
			$this->searchTerms[$documentField] = $value;			
		}
	}
	
	public function addSearchTerm($field, $value) {
		$documentFieldsAsValues = array_flip($this->mappedFields);
		
		if (array_key_exists($field, $documentFieldsAsValues)) {
			$documentFieldName = $documentFieldsAsValues[$field];
			
			$this->searchTerms[$documentFieldName] = $value;
		}
		
		return $this;
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

	public function addField($field) {
		$documentFieldsAsValues = array_flip($this->mappedFields);
		if (array_key_exists($field, $documentFieldsAsValues)) {
			$this->solrQuery->addField($documentFieldsAsValues[$field]);
		}
		
		return $this;
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
	public function getQueryString() {
		$term = '';
		if (count($this->searchTerms) == 0) {
			return $term;
		}
		
		$concat = 'AND';
		if (!$this->strict) {
			$concat = 'OR';
		}		
		
		$termCount = 1;
		foreach ($this->searchTerms as $fieldName => $fieldValue) {
			$term .= $fieldName .':*'.$fieldValue.'*';
			if ($termCount < count($this->searchTerms)) {
				$term .= ' '. $concat .' ';
			}
			
			$termCount++;
		}
		
		return $term;
	}
}

?>