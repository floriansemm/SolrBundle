<?php

namespace FS\SolrBundle\Logging;

interface SolrLoggerInterface
{
    /**
     * Called when the request is started
     *
     * @param string $request
     *
     * @return mixed
     */
    public function startRequest($request);

    /**
     * Called when the request has ended
     *
     * @return mixed
     */
    public function stopRequest();
}