<?php

namespace FS\SolrBundle\Doctrine\ORM\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use FS\SolrBundle\Doctrine\AbstractIndexingListener;
use FS\SolrBundle\Doctrine\Mapper\MetaInformationFactory;
use FS\SolrBundle\SolrInterface;
use Psr\Log\LoggerInterface;

class EntityIndexerSubscriber extends AbstractIndexingListener implements EventSubscriber
{

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return array('postUpdate', 'postPersist', 'preRemove');
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        $doctrineChangeSet = $args->getEntityManager()->getUnitOfWork()->getEntityChangeSet($entity);
        try {
            if ($this->hasChanged($doctrineChangeSet, $entity) === false) {
                return;
            }

            $this->solr->updateDocument($entity);
        } catch (\RuntimeException $e) {
            $this->logger->debug($e->getMessage());
        }
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        try {
            $this->solr->addDocument($entity);
        } catch (\RuntimeException $e) {
            $this->logger->debug($e->getMessage());
        }
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        try {
            $this->solr->removeDocument($entity);
        } catch (\RuntimeException $e) {
            $this->logger->debug($e->getMessage());
        }
    }
}