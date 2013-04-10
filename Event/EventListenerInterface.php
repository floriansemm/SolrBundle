<?php
namespace FS\SolrBundle\Event;

interface EventListenerInterface
{

    /**
     * @param Event $event
     */
    public function notify(Event $event);
}
