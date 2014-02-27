<?php
namespace FS\SolrBundle\Event\Listener;

use FS\SolrBundle\Event\Event;

class InsertLogListener extends AbstractLogListener
{

    /**
     * @param Event $event
     */
    public function onSolrInsert(Event $event)
    {
        $metaInformation = $event->getMetaInformation();

        $nameWithId = $this->createDocumentNameWithId($metaInformation);
        $fieldList = $this->createFieldList($metaInformation);

        $this->logger->debug(
            sprintf('document %s with fields %s was added', $nameWithId, $fieldList)
        );
    }
}
