<?php
namespace FS\SolrBundle\Doctrine\ORM\Listener;

use FS\SolrBundle\SolrFacade;
use Doctrine\ORM\Event\LifecycleEventArgs;

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
		$entity = $args->getEntity();
		
		try {
			$this->solrFacade->updateDocument($entity);
		} catch (\RuntimeException $e) {}
	}
}

?>