<?php
namespace FS\SolrBundle;

use FS\SolrBundle\Doctrine\Mapper\Command\CommandFactory;
use FS\SolrBundle\SolrQuery;
use FS\SolrBundle\Doctrine\Mapper\EntityMapper;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\DoctrineBundle\Registry;

class SolrQueryFacade {

	/**
	 * 
	 * @var \SolrQuery
	 */
	private $query = null;
	
	/**
	 * 
	 * @var Registry
	 */
	private $registry = null;
	
	/**
	 * 
	 * @var EntityManager
	 */
	private $em = null;
	
	/**
	 * 
	 * @var CommandFactory
	 */
	private $commandFactory = null;
	
	public function __construct(Registry $registry, CommandFactory $commandFactory) {
		$this->registry = $registry;
		$this->em = $registry->getEntityManager();
		
		$this->commandFactory = $commandFactory;
	}
		
	/**
	 * 
	 * @param string $entity
	 * @return SolrQuery
	 */
	public function createQuery($entity) {
		$class = $this->getClass($entity);
		$entity = new $class;
		
		$mapper = new EntityMapper();
		$command = $this->commandFactory->get('fresh');
		$mapper->setMappingCommand($command);
		$document = $mapper->toDocument($entity);
		
		$query = new SolrQuery();
		$query->setEntity($entity);
		$query->setMappedFields($command->getAnnotationReader()->getFieldMapping($entity));
		
		return $query;
	}
	
	private function getClass($entity) {
		if ($this->classExists($entity)) {
			return $entity;
		}
		
		list($namespaceAlias, $simpleClassName) = explode(':', $entity);
		$realClassName = $this->em->getConfiguration()->getEntityNamespace($namespaceAlias) . '\\' . $simpleClassName;
		
		if (!$this->classExists($realClassName)) {
			throw new \RuntimeException(sprintf('Unknown entity %s', $entity));
		}
		
		return $realClassName;
	}
	
	private function classExists($class) {
		return class_exists($class);
	}
}

?>