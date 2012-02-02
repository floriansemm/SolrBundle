<?php
namespace FS\SolrBundle\Event;

use Symfony\Component\HttpKernel\Log\LoggerInterface;

class DeleteLogListener implements EventListenerInterface {

	/**
	 * 
	 * @var LoggerInterface
	 */
	private $logger = null;
	
	public function __construct(LoggerInterface $logger) {
		$this->logger = $logger;
	}
	
	/* (non-PHPdoc)
	 * @see FS\SolrBundle\Event.EventListenerInterface::notify()
	 */
	public function notify(\SolrInputDocument $document) {
		$this->logger->info('document was deleted');
		
	}


}

?>