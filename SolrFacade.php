<?php
namespace FS\SolrBundle;

use FS\SolrBundle\Doctrine\Mapper\Command\CommandFactory;

use FS\SolrBundle\Query\DeleteDocumentQuery;

use FS\SolrBundle\Doctrine\Mapper\Command\CreateDeletedDocumentCommand;

use FS\SolrBundle\Doctrine\Annotation\AnnotationReader;

use FS\SolrBundle\Doctrine\Mapper\Command\CreateFromExistingDocumentCommand;

use FS\SolrBundle\Doctrine\Mapper\Command\CreateFreshDocumentCommand;

use FS\SolrBundle\Doctrine\Mapper\EntityMapper;

class SolrFacade {
	/**
	 * 
	 * @var \SolrClient
	 */
	private $solrClient = null;
	
	/**
	 * 
	 * @var EntityMapper
	 */
	private $entityMapper = null;
	
	/**
	 * 
	 * @var CommandFactory
	 */
	private $commandFactory = null;
		
	public function __construct(SolrConnection $connection, CommandFactory $commandFactory) {
		$this->solrClient = new \SolrClient($connection->getConnection());
		$this->commandFactory = $commandFactory;
		
		$this->entityMapper = new EntityMapper();
	}
	
	public function updateDocument($entity) {
		$command = $this->commandFactory->get('all');
		
		$this->entityMapper->setMappingCommand($command);
		
		$this->addDocumentToIndex($entity);		
	}
	
	public function removeDocument($entity) {
		$command = $this->commandFactory->get('identifier');
		
		$this->entityMapper->setMappingCommand($command);
		
		if ($document = $this->entityMapper->toDocument($entity)) {
			$deleteQuery = new DeleteDocumentQuery($document);
			$queryString = $deleteQuery->getQueryString();
			
			$response = $this->solrClient->deleteByQuery($queryString);
			
			$this->solrClient->commit();
		}
	}
	
	public function addDocument($entity) {
		$command = $this->commandFactory->get('all');
		
		$this->entityMapper->setMappingCommand($command);
		
		$this->addDocumentToIndex($entity);
	}
	
	private function addDocumentToIndex($entity) {
		$doc = $this->entityMapper->toDocument($entity);
		$updateResponse = $this->solrClient->addDocument($doc);
		
		$this->solrClient->commit();		
	}
	
	/**
	 * 
	 * @return \SolrResponse
	 */
	public function query(SolrQuery $query) {
		$solrQuery = $query->getSolrQuery();
		$response = $this->solrClient->query($solrQuery);
		
		$response = $response->getResponse();

		$targetEntity = $query->getEntity();
		$mappedEntities = array();
		if (array_key_exists('response', $response)) {
			if ($response['response']['docs'] !== false) {
				foreach ($response['response']['docs'] as $document) {
					$mappedEntities[] = $this->entityMapper->toEntity($document, $targetEntity);
				}
			}
		}
		
		return $mappedEntities;
	}
}
