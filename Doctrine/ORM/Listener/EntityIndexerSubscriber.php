<?php

namespace FS\SolrBundle\Doctrine\ORM\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use FS\SolrBundle\SolrInterface;

class EntityIndexerSubscriber implements EventSubscriber
{
    /**
     * @var SolrInterface
     */
    private $solr;

    /**
     * @param SolrInterface $solr
     */
    public function __construct(SolrInterface $solr)
    {
        $this->solr = $solr;
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
        }
    }
}