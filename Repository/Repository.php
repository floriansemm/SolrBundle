<?php
namespace FS\SolrBundle\Repository;

use FS\SolrBundle\Doctrine\Hydration\HydrationModes;
use FS\SolrBundle\Query\FindByDocumentNameQuery;
use FS\SolrBundle\Query\FindByIdentifierQuery;
use FS\SolrBundle\Query\QueryBuilderInterface;
use FS\SolrBundle\Solr;
use FS\SolrBundle\SolrInterface;

/**
 * Common repository class to find documents in the index
 */
class Repository implements RepositoryInterface
{

    /**
     * @var Solr
     */
    protected $solr = null;

    /**
     * @var object
     */
    protected $entity = null;

    /**
     * @var string
     */
    protected $hydrationMode = '';

    /**
     * @param SolrInterface $solr
     * @param object        $entity
     */
    public function __construct(SolrInterface $solr, $entity)
    {
        $this->solr = $solr;
        $this->entity = $entity;

        $this->hydrationMode = HydrationModes::HYDRATE_DOCTRINE;
    }

    /**
     * {@inheritdoc}
     */
    public function find($id)
    {
        $metaInformation = $this->solr->getMetaFactory()->loadInformation($this->entity);
        $metaInformation->setEntityId($id);

        $query = new FindByIdentifierQuery();
        $query->setIndex($metaInformation->getIndex());
        $query->setDocumentKey($metaInformation->getDocumentKey());
        $query->setEntity($this->entity);
        $query->setSolr($this->solr);
        $query->setHydrationMode($this->hydrationMode);
        $found = $this->solr->query($query);

        if (count($found) == 0) {
            return null;
        }

        return array_pop($found);
    }

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        $metaInformation = $this->solr->getMetaFactory()->loadInformation($this->entity);

        $query = new FindByDocumentNameQuery();
        $query->setRows(1000000);
        $query->setDocumentName($metaInformation->getDocumentName());
        $query->setIndex($metaInformation->getIndex());
        $query->setEntity($this->entity);
        $query->setSolr($this->solr);
        $query->setHydrationMode($this->hydrationMode);

        return $this->solr->query($query);
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(array $args)
    {
        $metaInformation = $this->solr->getMetaFactory()->loadInformation($this->entity);

        $query = $this->solr->createQuery($this->entity);
        $query->setHydrationMode($this->hydrationMode);
        $query->setRows(100000);
        $query->setUseAndOperator(true);
        $query->addSearchTerm('id', $metaInformation->getDocumentName() . '_*');
        $query->setQueryDefaultField('id');

        $helper = $query->getHelper();
        foreach ($args as $fieldName => $fieldValue) {
            $fieldValue = $helper->escapeTerm($fieldValue);

            $query->addSearchTerm($fieldName, $fieldValue);
        }

        return $this->solr->query($query);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $args)
    {
        $metaInformation = $this->solr->getMetaFactory()->loadInformation($this->entity);

        $query = $this->solr->createQuery($this->entity);
        $query->setHydrationMode($this->hydrationMode);
        $query->setRows(1);
        $query->setUseAndOperator(true);
        $query->addSearchTerm('id', $metaInformation->getDocumentName() . '_*');
        $query->setQueryDefaultField('id');

        $helper = $query->getHelper();
        foreach ($args as $fieldName => $fieldValue) {
            $fieldValue = $helper->escapeTerm($fieldValue);

            $query->addSearchTerm($fieldName, $fieldValue);
        }

        $found = $this->solr->query($query);

        return array_pop($found);
    }

    /**
     * @param string $entity
     *
     * @return QueryBuilderInterface
     */
    public function getQueryBuilder($entity)
    {
        return $this->solr->getQueryBuilder($entity);
    }
}
