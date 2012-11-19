<?php
namespace FS\SolrBundle\Doctrine\MongoDB\Listener;

use FS\SolrBundle\SolrQueryFacade;

use FS\SolrBundle\SolrFacade;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;

class UpdateDocumentListener {
    
    /**
     * @var SolrFacade
     */
    private $solrFacade = null;
    
    /**
     * @param SolrFacade $solrFacade
     */
    public function __construct(SolrFacade $solrFacade) {
        $this->solrFacade = $solrFacade;
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args) {
        $entity = $args->getDocument();
        
        try {
            $this->solrFacade->updateDocument($entity);
        } catch (\RuntimeException $e) {}
    }
}

?>