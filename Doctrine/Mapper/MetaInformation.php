<?php
namespace FS\SolrBundle\Doctrine\Mapper;

use FS\SolrBundle\Doctrine\Annotation\Field;

class MetaInformation {
	
	/**
	 * @var string
	 */
	private $identifier = '';
	
	/**
	 * @var string
	 */	
	private $className = '';
	
	/**
	 * @var string
	 */	
	private $documentName = '';

	/**
	 * @var array
	 */	
	private $fields = array();
	
	/**
	 * @var array
	 */	
	private $fieldMapping = array();
	
	/**
	 * @var string
	 */	
	private $repository = '';
	
	/**
	 * @var object
	 */	
	private $entity = null;
	
	/**
	 * 
	 * @return number
	 */	
	public function getEntityId() {
		if ($this->entity !== null) {
			return $this->entity->getId();
		}
		
		return 0;
	}
	
	/**
	 * @return string
	 */
	public function getIdentifier() {
		return $this->identifier;
	}

	/**
	 * @return string
	 */
	public function getClassName() {
		return $this->className;
	}

	/**
	 * @return string
	 */
	public function getDocumentName() {
		return $this->documentName;
	}

	/**
	 * @return array With instances of FS\SolrBundle\Doctrine\Annotation\Field
	 */
	public function getFields() {
		return $this->fields;
	}

	/**
	 * @return string
	 */
	public function getRepository() {
		return $this->repository;
	}

	/**
	 * @return object
	 */
	public function getEntity() {
		return $this->entity;
	}

	/**
	 * @param string $identifiert
	 */
	public function setIdentifier($identifier) {
		$this->identifier = $identifier;
	}

	/**
	 * @param string $className
	 */
	public function setClassName($className) {
		$this->className = $className;
	}

	/**
	 * @param string $documentName
	 */
	public function setDocumentName($documentName) {
		$this->documentName = $documentName;
	}

	/**
	 * @param multitype: $fields
	 */
	public function setFields($fields) {
		$this->fields = $fields;
	}
	
	/**
	 * @param string $field
	 * @return boolean
	 */
	public function hasField($field) {
		if (count($this->fields) == 0) {
			return false;
		}
		
		return isset($this->fields[$field]);
	}
	
	/**
	 * @param string $field
	 * @param string $value
	 */
	public function setFieldValue($field, $value) {	
		$this->fields[$field]->value = $value;
	}
	
	/**
	 * @param unknown_type $field
	 * @return Field|null
	 */
	public function getField($field) {
		if (!$this->hasField($field)) {
			return null;
		}
		
		return $this->fields[$field];
	}

	/**
	 * @param string $repository
	 */
	public function setRepository($repository) {
		$this->repository = $repository;
	}

	/**
	 * @param object $entity
	 */
	public function setEntity($entity) {
		$this->entity = $entity;
	}
	
	/**
	 * @return array
	 */
	public function getFieldMapping() {
		return $this->fieldMapping;
	}

	/**
	 * @param array $fieldMapping
	 */
	public function setFieldMapping($fieldMapping) {
		$this->fieldMapping = $fieldMapping;
	}
}

?>