<?php
namespace FS\SolrBundle\Event\Listener;

use FS\SolrBundle\Doctrine\Mapper\MetaInformation;
use FS\SolrBundle\Event\Listener\AbstractLogListener;

class UpdateLogListener extends AbstractLogListener  {
	
	/* (non-PHPdoc)
	 * @see FS\SolrBundle\Event.EventListenerInterface::notify()
	 */
	public function notify(MetaInformation $metaInformation) {
		$nameWithId = $this->createDocumentNameWithId($metaInformation);
		$fieldList = $this->createFieldList($metaInformation);
		
		$this->logger->info(sprintf('%s with fields %s was updated', $nameWithId, $fieldList));
	}
}

?>