<?php
namespace FS\SolrBundle;

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
	 * @var LoggerInterface
	 */
	private $logger = null;
	
	/**
	 * 
	 * @var Configuration
	 */
	private $doctrineConfiguration = null;
	
	/**
	 * 
	 * @var EventManager
	 */
	private $manager = null;
	
	public function __construct(SolrConnection $connection, CommandFactory $commandFactory, EventManager $manager) {
		$this->solrClient = $connection->getClient();
		$this->commandFactory = $commandFactory;
		$this->manager = $manager;
		
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
	
	public function setDoctrineConfiguration(Configuration $doctrineConfiguration) {
		$this->doctrineConfiguration = $doctrineConfiguration;
	}
	
	/**
	 * 
	 * @param SolrQuery $entity
	 */
	public function createQuery($entity) {
		$class = $this->getClass($entity);
		$entity = new $class;
		
		$query = new SolrQuery($this);
		$query->setEntity($entity);
		
		$command = $this->commandFactory->get('all');
		$query->setMappedFields($command->getAnnotationReader()->getFieldMapping($entity));
		
		return $query;
	}
	
	/**
	 * 
	 * @param RepositoryInterface repositoryClassity
	 */
	public function getRepository($entityAlias) {
		$class = $this->getClass($entityAlias);
		$entity = new $class;

		$annotationReader = new AnnotationReader();
		$repositoryClass = $annotationReader->getRepository($entity);
		
		if (class_exists($repositoryClass)) {
			$repositoryInstance = new $repositoryClass($this, $entity);
			
			if ($repositoryInstance instanceof Repository) {
				return $repositoryInstance;
			}
			
			throw new \RuntimeException(sprintf('%s must extends the FS\SolrBundle\Repository\Repository', $repositoryClass));
		}
		
		return new Repository($this, $entity);
	}
	
	private function getClass($entity) {
		if (is_object($entity) || class_exists($entity)) {
			return $entity;
		}
	
		list($namespaceAlias, $simpleClassName) = explode(':', $entity);
		$realClassName = $this->doctrineConfiguration->getEntityNamespace($namespaceAlias) . '\\' . $simpleClassName;
	
		if (!class_exists($realClassName)) {
			throw new \RuntimeException(sprintf('Unknown entity %s', $entity));
		}
	
		return $realClassName;
	}
	
	public function removeDocument($entity) {
		$command = $this->commandFactory->get('identifier');
		
		$this->entityMapper->setMappingCommand($command);
		
		if ($document = $this->entityMapper->toDocument($entity)) {
			$deleteQuery = new FindByIdentifierQuery($document);
			$queryString = $deleteQuery->getQueryString();

			try {
				$response = $this->solrClient->deleteByQuery($queryString);
				
				$this->solrClient->commit();
			} catch (\Exception $e) {}
			
			$this->manager->handle(EventManager::DELETE, $document);
		}
	}
	
	public function updateDocument($entity) {
		$doc = $this->mapEntityToDocument($entity);
		
		$this->manager->handle(EventManager::UPDATE, $doc);
		
		$this->addDocumentToIndex($doc);
	}	
	
	public function addDocument($entity) {
		$doc = $this->mapEntityToDocument($entity);
		
		$this->manager->handle(EventManager::INSERT, $doc);
		
		$this->addDocumentToIndex($doc);
	}

	/**
	 * 
	 * @param object $entity
	 * @return Ambigous <SolrInputDocument, NULL>
	 */
	private function mapEntityToDocument($entity) {
		$command = $this->commandFactory->get('all');
		$this->entityMapper->setMappingCommand($command);
		$doc = $this->entityMapper->toDocument($entity);

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
