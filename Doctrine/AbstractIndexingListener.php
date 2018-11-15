<?php

namespace FS\SolrBundle\Doctrine;

use Doctrine\ORM\Event\LifecycleEventArgs;
use FS\SolrBundle\Doctrine\Mapper\MetaInformationFactory;
use FS\SolrBundle\Doctrine\Mapper\SolrMappingException;
use FS\SolrBundle\SolrInterface;
use Psr\Log\LoggerInterface;

class AbstractIndexingListener
{
    /**
     * @var SolrInterface
     */
    protected $solr;

    /**
     * @var MetaInformationFactory
     */
    protected $metaInformationFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param SolrInterface          $solr
     * @param MetaInformationFactory $metaInformationFactory
     * @param LoggerInterface        $logger
     */
    public function __construct(SolrInterface $solr, MetaInformationFactory $metaInformationFactory, LoggerInterface $logger)
    {
        $this->solr = $solr;
        $this->metaInformationFactory = $metaInformationFactory;
        $this->logger = $logger;
    }

    /**
     * @param array  $doctrineChangeSet
     * @param object $entity
     *
     * @return bool
     */
    protected function hasChanged($doctrineChangeSet, $entity)
    {
        if (empty($doctrineChangeSet)) {
            return false;
        }

        $metaInformation = $this->metaInformationFactory->loadInformation($entity);

        $documentChangeSet = array();

        /* Check all Solr fields on this entity and check if this field is in the change set */
        foreach ($metaInformation->getFields() as $field) {
            if (array_key_exists($field->name, $doctrineChangeSet)) {
                $documentChangeSet[] = $field->name;
            }
        }

        return count($documentChangeSet) > 0;
    }

    /**
     * @param object $entity
     *
     * @return bool
     */
    protected function isNested($entity)
    {
        $metaInformation = $this->metaInformationFactory->loadInformation($entity);

        return $metaInformation->isNested();
    }

    /**
     * @param object $entity
     *
     * @return bool
     */
    protected function isAbleToIndex($entity)
    {
        try {
            $metaInformation = $this->metaInformationFactory->loadInformation($entity);
        } catch (SolrMappingException $e) {
            return false;
        }

        return true;
    }
}