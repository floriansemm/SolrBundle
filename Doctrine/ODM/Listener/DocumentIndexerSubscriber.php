<?php

namespace FS\SolrBundle\Doctrine\ODM\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use FS\SolrBundle\Doctrine\AbstractIndexingListener;
use FS\SolrBundle\SolrInterface;
use Psr\Log\LoggerInterface;

class DocumentIndexerSubscriber extends AbstractIndexingListener implements EventSubscriber
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
        $document = $args->getDocument();

        try {
            $doctrineChangeSet = $args->getDocumentManager()->getUnitOfWork()->getDocumentChangeSet($document);

            if ($this->hasChanged($doctrineChangeSet, $document) == false) {
                return;
            }

            $this->solr->updateDocument($document);
        } catch (\RuntimeException $e) {
            $this->logger->debug($e->getMessage());
        }
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getDocument();

        try {
            $this->solr->removeDocument($entity);
        } catch (\RuntimeException $e) {
            $this->logger->debug($e->getMessage());
        }
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getDocument();

        try {
            $this->solr->addDocument($entity);
        } catch (\RuntimeException $e) {
            $this->logger->debug($e->getMessage());
        }
    }
}