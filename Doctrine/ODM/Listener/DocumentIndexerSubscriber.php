<?php

namespace FS\SolrBundle\Doctrine\ODM\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use FS\SolrBundle\SolrInterface;
use Psr\Log\LoggerInterface;

class DocumentIndexerSubscriber implements EventSubscriber
{
    /**
     * @var SolrInterface
     */
    private $solr = null;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param SolrInterface   $solr
     * @param LoggerInterface $logger
     */
    public function __construct(SolrInterface $solr, LoggerInterface $logger)
    {
        $this->solr = $solr;
        $this->logger = $logger;
    }

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

            if (count($this->solr->computeChangeSet($doctrineChangeSet, $document)) === 0) {
                return;
            }

            $this->solr->updateDocument($document);
        } catch (\RuntimeException $e) {
            $this->logger->critical($e->getMessage());
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
            $this->logger->critical($e->getMessage());
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
            $this->logger->critical($e->getMessage());
        }
    }
}