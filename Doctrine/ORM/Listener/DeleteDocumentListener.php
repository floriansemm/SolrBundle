<?php
namespace FS\SolrBundle\Doctrine\ORM\Listener;

use FS\SolrBundle\SolrFacade;
use Doctrine\ORM\Event\LifecycleEventArgs;

class DeleteDocumentListener {
	
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
	public function preRemove(LifecycleEventArgs $args) {
		$entity = $args->getEntity();
		
		try {
			$this->solrFacade->removeDocument($entity);
		} catch (\RuntimeException $e) {}		
	}
}

?>