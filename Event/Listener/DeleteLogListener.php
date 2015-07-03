<?php

namespace FS\SolrBundle\Event\Listener;

use FS\SolrBundle\Event\Event;

/**
 * Create a log-entry if a document was deleted
 */
class DeleteLogListener extends AbstractLogListener
{

    /**
     * @param Event $event
     */
    public function onSolrDelete(Event $event)
    {
        $metaInformation = $event->getMetaInformation();

        $nameWithId = $this->createDocumentNameWithId($metaInformation);
        $fieldList = $this->createFieldList($metaInformation);

        $this->logger->debug(
            sprintf('document %s with fields %s was deleted', $nameWithId, $fieldList)
        );
    }
}
