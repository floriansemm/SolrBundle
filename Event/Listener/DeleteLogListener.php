<?php
namespace FS\SolrBundle\Event\Listener;

use FS\SolrBundle\Event\Event;
use FS\SolrBundle\Doctrine\Mapper\MetaInformation;

class DeleteLogListener extends AbstractLogListener {
	
	/**
	 * (non-PHPdoc)
	 * @see \FS\SolrBundle\Event\EventListenerInterface::notify()
	 */
	public function notify(Event $event) {
		$metaInformation = $event->getMetaInformation();
		
		$nameWithId = $this->createDocumentNameWithId($metaInformation);
		$fieldList = $this->createFieldList($metaInformation);
		
		$this->logger->info(sprintf('use path %s, document %s with fields %s was deleted', $event->getCore(), $nameWithId, $fieldList));
	}
}

?>