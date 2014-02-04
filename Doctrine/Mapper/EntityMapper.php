<?php

namespace FS\SolrBundle\Doctrine\Mapper;

use FS\SolrBundle\Doctrine\Mapper\Mapping\AbstractDocumentCommand;
use FS\SolrBundle\Doctrine\Annotation\Index as Solr;
use Solarium\QueryType\Update\Query\Document\Document;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Solarium\Client;

class EntityMapper {

	/**
	 * @var CreateDocumentCommandInterface
	 */
	private $mappingCommand = null;

	/**
	 * @var EventDispatcherInterface
	 */
	private $eventDispatcher;

	/**
	 * @var Client
	 */
	private $client;

	function __construct(EventDispatcherInterface $eventDispatcher, Client $client = null) {
		$this->client = $client;
		$this->eventDispatcher = $eventDispatcher;
	}

	/**
	 * @param AbstractDocumentCommand $command
	 */
	public function setMappingCommand(AbstractDocumentCommand $command) {
		$this->mappingCommand = $command;
	}

	/**
	 * @param object $entity
	 * @return Document
	 */
	public function toDocument(MetaInformation $meta) {
		if ($this->mappingCommand instanceof AbstractDocumentCommand) {
			return $this->mappingCommand->createDocument($meta);
		}

		return null;
	}

	/**
	 * @param \ArrayAccess $document
	 * @param object $targetEntity
	 * @return object
	 */
	public function toEntity(\ArrayAccess $document, MetaInformation $meta) {
		if (null === $sourceTargetEntity) {
			throw new \InvalidArgumentException('$sourceTargetEntity should not be null');
		}

		$entityHydrateEvent = new \FS\SolrBundle\Event\EntityHydrate($document, $this->client, $meta);

		return $this->eventDispatcher->dispatch(\FS\SolrBundle\Event\Events::SOLR_HYDRATE_ENTITY, $entityHydrateEvent)->getEntity();
	}



}
