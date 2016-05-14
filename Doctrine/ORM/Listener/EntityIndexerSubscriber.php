<?php

namespace FS\SolrBundle\Doctrine\ORM\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use FS\SolrBundle\SolrInterface;
use Psr\Log\LoggerInterface;

class EntityIndexerSubscriber implements EventSubscriber
{
    /**
     * @var SolrInterface
     */
    private $solr;

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
     *
     * @return bool
     */
    private function hasChanged(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        $doctrineChangeSet = $args->getEntityManager()->getUnitOfWork()->getEntityChangeSet($entity);

        return count($this->solr->computeChangeSet($doctrineChangeSet, $entity)) > 0;
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        try {
            if ($this->hasChanged($args) === false) {
                return;
            }

            $this->solr->updateDocument($entity);
        } catch (\RuntimeException $e) {
            $this->logger->critical($e->getMessage());
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
            $this->logger->critical($e->getMessage());
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
            $this->logger->critical($e->getMessage());
        }
    }
}