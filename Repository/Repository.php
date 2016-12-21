<?php
namespace FS\SolrBundle\Repository;

use FS\SolrBundle\Doctrine\Hydration\HydrationModes;
use FS\SolrBundle\Doctrine\Mapper\MetaInformationInterface;
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
     * @var MetaInformationInterface
     */
    protected $metaInformation = null;

    /**
     * @var string
     */
    protected $hydrationMode = '';

    /**
     * @param SolrInterface            $solr
     * @param MetaInformationInterface $metaInformation
     */
    public function __construct(SolrInterface $solr, MetaInformationInterface $metaInformation)
    {
        $this->solr = $solr;
        $this->metaInformation = $metaInformation;

        $this->hydrationMode = HydrationModes::HYDRATE_DOCTRINE;
    }

    /**
     * {@inheritdoc}
     */
    public function find($id)
    {
        $query = new FindByIdentifierQuery();
        $query->setIndex($this->metaInformation->getIndex());
        $query->setDocumentKey($this->metaInformation->getDocumentKey());
        $query->setEntity($this->metaInformation->getEntity());
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
        $query = new FindByDocumentNameQuery();
        $query->setRows(1000000);
        $query->setDocumentName($this->metaInformation->getDocumentName());
        $query->setIndex($this->metaInformation->getIndex());
        $query->setEntity($this->metaInformation->getEntity());
        $query->setSolr($this->solr);
        $query->setHydrationMode($this->hydrationMode);

        return $this->solr->query($query);
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(array $args)
    {
        $query = $this->solr->createQuery($this->metaInformation->getEntity());
        $query->setHydrationMode($this->hydrationMode);
        $query->setRows(100000);
        $query->setUseAndOperator(true);
        $query->addSearchTerm('id', $this->metaInformation->getDocumentName() . '_*');
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
        $query = $this->solr->createQuery($this->metaInformation->getEntity());
        $query->setHydrationMode($this->hydrationMode);
        $query->setRows(1);
        $query->setUseAndOperator(true);
        $query->addSearchTerm('id', $this->metaInformation->getDocumentName() . '_*');
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
     * @return QueryBuilderInterface
     */
    public function getQueryBuilder()
    {
        return $this->solr->getQueryBuilder($this->metaInformation->getEntity());
    }
}
