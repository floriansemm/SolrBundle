<?php

namespace FS\SolrBundle\Logging;

interface SolrLoggerInterface
{
    /**
     * Called when the request is started
     *
     * @param array $request
     */
    public function startRequest(array $request);

    /**
     * Called when the request has ended
     */
    public function stopRequest();
}