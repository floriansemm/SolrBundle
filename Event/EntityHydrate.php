<?php

namespace FS\SolrBundle\Event;

/**
 * Description of EntityHydrate
 *
 * @author Volker von Hoesslin <volker.von.hoesslin@empora.com>
 */
class EntityHydrate extends Event {

	private $entity;
	private $document;

	public function __construct($document, $client = null, MetaInformation $metainformation = null, $solrAction = '') {
		parent::__construct($client, $metainformation, $solrAction);
		$this->document = $document;
	}

	/**
	 * @return mixed
	 */
	public function getEntity() {
		return $this->entity;
	}

	/**
	 * @param mixed $entity
	 */
	public function setEntity($entity) {
		$this->entity = $entity;
	}

	public function getDocument() {
		return $this->document;
	}

}
