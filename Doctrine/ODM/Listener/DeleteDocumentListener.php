<?php
namespace FS\SolrBundle\Doctrine\ODM\Listener;

use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use FS\SolrBundle\SolrFacade;
use FS\SolrBundle\SolrQueryFacade;

class DeleteDocumentListener
{

    /**
     * @var SolrFacade
     */
    private $solrFacade = null;

    /**
     * @param SolrFacade $solrFacade
     */
    public function __construct(SolrFacade $solrFacade)
    {
        $this->solrFacade = $solrFacade;
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getDocument();

        try {
            $this->solrFacade->removeDocument($entity);
        } catch (\RuntimeException $e) {
        }
    }
}

?>