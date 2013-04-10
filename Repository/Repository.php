<?php
namespace FS\SolrBundle\Repository;

use FS\SolrBundle\Query\FindByDocumentNameQuery;
use FS\SolrBundle\Query\FindByIdentifierQuery;
use FS\SolrBundle\SolrFacade;

class Repository implements RepositoryInterface
{

    /**
     * @var SolrFacade
     */
    private $solr = null;

    /**
     * @var object
     */
    private $entity = null;

    /**
     * @param SolrFacade $solr
     * @param object $entity
     */
    public function __construct(SolrFacade $solr, $entity)
    {
        $this->solr = $solr;

        $this->entity = $entity;
    }

    /* (non-PHPdoc)
     * @see FS\SolrBundle\Repository.RepositoryInterface::find()
     */
    public function find($id)
    {
        $this->entity->setId($id);

        $mapper = $this->solr->getMapper();
        $mapper->setMappingCommand($this->solr->getCommandFactory()->get('all'));
        $metaInformation = $this->solr->getMetaFactory()->loadInformation($this->entity);

        $document = $mapper->toDocument($metaInformation);

        $query = new FindByIdentifierQuery($document);
        $query->setEntity($this->entity);
        $found = $this->solr->query($query);

        if (count($found) == 0) {
            return null;
        }

        return array_pop($found);
    }

    /* (non-PHPdoc)
     * @see FS\SolrBundle\Repository.RepositoryInterface::findAll()
     */
    public function findAll()
    {
        $mapper = $this->solr->getMapper();
        $mapper->setMappingCommand($this->solr->getCommandFactory()->get('all'));
        $metaInformation = $this->solr->getMetaFactory()->loadInformation($this->entity);

        $document = $mapper->toDocument($metaInformation);

        if (null === $document) {
            return null;
        }

        $document->deleteField('id');

        $query = new FindByDocumentNameQuery($document);
        $query->setEntity($this->entity);

        return $this->solr->query($query);
    }

    /* (non-PHPdoc)
     * @see FS\SolrBundle\Repository.RepositoryInterface::findBy()
     */
    public function findBy(array $args)
    {
        $query = $this->solr->createQuery($this->entity);

        foreach ($args as $fieldName => $fieldValue) {
            $query->addSearchTerm($fieldName, $fieldValue);
        }

        return $this->solr->query($query);
    }

    /* (non-PHPdoc)
     * @see FS\SolrBundle\Repository.RepositoryInterface::findOneBy()
     */
    public function findOneBy(array $args)
    {
        $found = $this->findBy($args);

        return array_pop($found);
    }
}
