<?php
namespace FS\SolrBundle\Doctrine\ORM\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use FS\SolrBundle\Solr;

class AddDocumentListener
{

    /**
     * @var Solr
     */
    private $solr = null;

    /**
     * @param Solr $solr
     */
    public function __construct(Solr $solr)
    {
        $this->solr = $solr;
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
}
