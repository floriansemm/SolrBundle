<?php
namespace FS\SolrBundle\Doctrine\Mapper;

class MetaInformation {
	private $identifier = '';
	
	private $className = '';
	
	private $documentName = '';
	
	private $fields = array();
	
	private $fieldMapping = array();
	
	private $repository = '';
	
	private $entity = null;
	
	public function getEntityId() {
		if ($this->entity !== null) {
			return $this->entity->getId();
		}
		
		return 0;
	}
	
	/**
	 * @return the $identifiert
	 */
	public function getIdentifier() {
		return $this->identifier;
	}

	/**
	 * @return the $className
	 */
	public function getClassName() {
		return $this->className;
	}

	/**
	 * @return the $documentName
	 */
	public function getDocumentName() {
		return $this->documentName;
	}

	/**
	 * @return the $fields
	 */
	public function getFields() {
		return $this->fields;
	}

	/**
	 * @return the $repository
	 */
	public function getRepository() {
		return $this->repository;
	}

	/**
	 * @return the $entity
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
	 * @param string $repository
	 */
	public function setRepository($repository) {
		$this->repository = $repository;
	}

	/**
	 * @param NULL $entity
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