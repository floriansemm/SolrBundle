<?php
namespace FS\SolrBundle;

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
	
	public function __construct(SolrConnection $connection, CommandFactory $commandFactory, LoggerInterface $logger) {
		$this->solrClient = $connection->getClient();
		$this->commandFactory = $commandFactory;
		$this->logger = $logger;
		
		$this->entityMapper = new EntityMapper();
	}
	
	public function setDoctrineConfiguration(Configuration $doctrineConfiguration) {
		$this->doctrineConfiguration = $doctrineConfiguration;
	}
	
	public function createQuery($entity) {
		$class = $this->getClass($entity);
		$entity = new $class;
		
		$query = new SolrQuery($this);
		$query->setEntity($entity);
		
		$command = $this->commandFactory->get('all');
		$query->setMappedFields($command->getAnnotationReader()->getFieldMapping($entity));
		
		return $query;
	}
	
	private function getClass($entity) {
		if (class_exists($entity)) {
			return $entity;
		}
	
		list($namespaceAlias, $simpleClassName) = explode(':', $entity);
		$realClassName = $this->doctrineConfiguration->getEntityNamespace($namespaceAlias) . '\\' . $simpleClassName;
	
		if (!class_exists($realClassName)) {
			throw new \RuntimeException(sprintf('Unknown entity %s', $entity));
		}
	
		return $realClassName;
	}
	
	public function updateDocument($entity) {
		$this->addDocument($entity);	
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
				
				$this->logger->info(sprintf('delete document with id %s', $entity->getId()));
			} catch (\Exception $e) {
				$this->logger->err(sprintf('could not delete document with ID %s, solr-error:'.$e->getMessage(), $entity->getId()));
			}
		}
	}
	
	public function addDocument($entity) {
		$command = $this->commandFactory->get('all');
		
		$this->entityMapper->setMappingCommand($command);
		
		$this->addDocumentToIndex($entity);
	}
	
	private function addDocumentToIndex($entity) {
		$doc = $this->entityMapper->toDocument($entity);
		
		try {
			$updateResponse = $this->solrClient->addDocument($doc);
			
			$this->solrClient->commit();
			
			$this->logger->info(sprintf('add document with id %s to index', $entity->getId()));
		} catch (\Exception $e) { 
			$this->logger->err($e->getMessage());
			
			throw new \RuntimeException('could not index entity');
		}		
	}
		
	/**
	 * 
	 * @return array
	 */
	public function query(SolrQuery $query) {
		$solrQuery = $query->getSolrQuery();
		
		try {
			$response = $this->solrClient->query($solrQuery);
			
			$this->logger->info(sprintf('query index, query: %s', $query->getQueryString()));
		} catch (\Exception $e) {
			$this->logger->err(sprintf('the query %s cased an error', $query->getQueryString()));
			
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
			
			$this->logger->info('clear the index successful');
		} catch (\Exception $e) {
			throw new \RuntimeException('could not clear index');
		}
	}
	
	public function synchronizeIndex($entity) {
		$this->updateDocument($entity);
	}	
}
