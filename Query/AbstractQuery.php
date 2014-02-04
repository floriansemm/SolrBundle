<?php

namespace FS\SolrBundle\Query;

use FS\SolrBundle\Doctrine\Mapper\MetaInformation;
use Solarium\QueryType\Select\Query\Query;
use Solarium\QueryType\Update\Query\Document\Document;

abstract class AbstractQuery extends Query {

	/**
	 * @var Document
	 */
	protected $document = null;

	/**
	 * @var MetaInformation
	 */
	private $entityMetaInformation = null;

	/**
	 * @return MetaInformation
	 */
	public function getEntityMetaInformation() {
		return $this->entityMetaInformation;
	}

	/**
	 * @param MetaInformation $entityMetaInformation
	 */
	public function setEntityMetaInformation($entityMetaInformation) {
		$this->entityMetaInformation = $entityMetaInformation;
	}

	/**
	 * @param Document $document
	 */
	public function setDocument($document) {
		$this->document = $document;
	}

	/**
	 * @return Document
	 */
	public function getDocument() {
		return $this->document;
	}

}
