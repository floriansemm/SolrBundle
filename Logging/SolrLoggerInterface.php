<?php

/**
 * Created by PhpStorm.
 * User: zach
 * Date: 1/28/16
 * Time: 9:59 AM
 */

namespace FS\SolrBundle\Logging;

/**
 * Interface SolrLogger
 *
 * @package FS\SolrBundle\Logging
 */
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