<?php

namespace FS\SolrBundle\Builder;

use Solarium\Client;

/**
 * Creates an instance of the Solarium Client
 */
class SolrBuilder implements Builder
{
    /**
     * @var array
     */
    private $settings = array();

    /**
     * @param array $settings
     */
    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @return Client
     */
    public function build()
    {
        return new Client(array('endpoint' => $this->settings));
    }
} 