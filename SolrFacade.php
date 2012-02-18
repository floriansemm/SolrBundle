<?php
namespace FS\SolrBundle;

use FS\SolrBundle\Doctrine\Mapper\MetaInformationFactory;

use FS\SolrBundle\Event\EventManager;

use FS\SolrBundle\Query\AbstractQuery;

use FS\SolrBundle\Repository\Repository;

use Doctrine\ORM\Configuration;

use FS\SolrBundle\Query\SolrQuery;

use FS\SolrBundle\Query\FindByIdentifierQuery;

use Symfony\Component\HttpKernel\Log\LoggerInterface;
use FS\SolrBundle\Doctrine\Mapper\Mapping\CommandFactory;
use FS\SolrBundle\Doctrine\Annotation\AnnotationReader;
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
		
	/**
	 * 
	 * @var EventManager
	 */
	private $eventManager = null;
	
	/**
	 * 
	 * @var MetaInformationFactory
	 */
	private $metaInformationFactory = null;
	
	/**
	 * 
	 * @param SolrConnection $connection
	 * @param CommandFactory $commandFactory
	 * @param EventManager $manager
	 * @param MetaInformationFactory $metaInformationFactory
	 */
	public function __construct(SolrConnection $connection, CommandFactory $commandFactory, EventManager $manager, MetaInformationFactory $metaInformationFactory) {
		$this->solrClient = $connection->getClient();
		$this->commandFactory = $commandFactory;
		$this->eventManager = $manager;
		$this->metaInformationFactory = $metaInformationFactory;
		
		$this->entityMapper = new EntityMapper();
	}
	
	/**
	 * @return EntityMapper
	 */
	public function getMapper() {
		return $this->entityMapper;
	}
	
	/**
	 * @return CommandFactory
	 */
	public function getCommandFactory() {
		return $this->commandFactory;
	}
		
	/**
	 * 
	 * @return MetaInformationFactory
	 */
	public function getMetaFactory() {
		return $this->metaInformationFactory;
	}
	
	/**
	 * 
	 * @param object $entity
	 * @return SolrQuery
	 */
	public function createQuery($entity) {
		$metaInformation = $this->metaInformationFactory->loadInformation($entity);
		$class = $metaInformation->getClassName();
		$entity = new $class;
		
		$query = new SolrQuery($this);
		$query->setEntity($entity);
		
		$command = $this->commandFactory->get('all');
		$query->setMappedFields($metaInformation->getFieldMapping());
		
		return $query;
	}
	
	/**
	 * 
	 * @param RepositoryInterface repositoryClassity
	 */
	public function getRepository($entityAlias) {
		$metaInformation = $this->metaInformationFactory->loadInformation($entityAlias);
		$class = $metaInformation->getClassName();
		
		$entity = new $class;

		$repositoryClass = $metaInformation->getRepository();
		if (class_exists($repositoryClass)) {
			$repositoryInstance = new $repositoryClass($this, $entity);
			
			if ($repositoryInstance instanceof Repository) {
				return $repositoryInstance;
			}
			
			throw new \RuntimeException(sprintf('%s must extends the FS\SolrBundle\Repository\Repository', $repositoryClass));
		}
		
		return new Repository($this, $entity);
	}
	
	public function removeDocument($entity) {
		$command = $this->commandFactory->get('identifier');
		
		$this->entityMapper->setMappingCommand($command);
		
		$metaInformations = $this->metaInformationFactory->loadInformation($entity);
		
		if ($document = $this->entityMapper->toDocument($metaInformations)) {
			$deleteQuery = new FindByIdentifierQuery($document);
			$queryString = $deleteQuery->getQueryString();

			try {
				$response = $this->solrClient->deleteByQuery($queryString);
				
				$this->solrClient->commit();
			} catch (\Exception $e) {}
			
			$this->eventManager->handle(EventManager::DELETE, $document);
		}
	}
	
	public function updateDocument($entity) {
		$doc = $this->mapEntityToDocument($entity);
		
		$this->eventManager->handle(EventManager::UPDATE, $doc);
		
		$this->addDocumentToIndex($doc);
	}	
	
	public function addDocument($entity) {
		$doc = $this->mapEntityToDocument($entity);
		
		$this->eventManager->handle(EventManager::INSERT, $doc);
		
		$this->addDocumentToIndex($doc);
	}

	/**
	 * 
	 * @param object $entity
	 * @return Ambigous <SolrInputDocument, NULL>
	 */
	private function mapEntityToDocument($entity) {
		$command = $this->commandFactory->get('all');

		$metaInformations = $this->metaInformationFactory->loadInformation($entity);
		
		$this->entityMapper->setMappingCommand($command);
		$doc = $this->entityMapper->toDocument($metaInformations);

		return $doc;
	}
	
	private function addDocumentToIndex($doc) {
		try {
			$updateResponse = $this->solrClient->addDocument($doc);
			
			$this->solrClient->commit();
			
		} catch (\Exception $e) { 
			throw new \RuntimeException('could not index entity');
		}		
	}
		
	/**
	 * 
	 * @return array
	 */
	public function query(AbstractQuery $query) {
		$solrQuery = $query->getSolrQuery();
		
		try {
			$response = $this->solrClient->query($solrQuery);
		} catch (\Exception $e) {
			return array();
		}
		
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
	
	public function clearIndex() {
		try {
			$this->solrClient->deleteByQuery('*:*');
			$this->solrClient->commit();
			
		} catch (\Exception $e) {
			throw new \RuntimeException('could not clear index');
		}
	}
	
	public function synchronizeIndex($entity) {
		$this->updateDocument($entity);
	}	
}
