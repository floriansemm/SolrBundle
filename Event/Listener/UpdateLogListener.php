<?php
namespace FS\SolrBundle\Event\Listener;

use FS\SolrBundle\Event\Event;

class UpdateLogListener extends AbstractLogListener
{

    /**
     * @param Event $event
     */
    public function onSolrUpdate(Event $event)
    {
        $metaInformation = $event->getMetaInformation();

        $nameWithId = $this->createDocumentNameWithId($metaInformation);
        $fieldList = $this->createFieldList($metaInformation);

        $this->logger->debug(
            sprintf('document %s with fields %s was updated', $nameWithId, $fieldList)
        );
    }
}
