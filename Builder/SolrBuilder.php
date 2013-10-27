<?php

namespace FS\SolrBundle\Builder;

use Solarium\Client;

class SolrBuilder implements Builder
{
    private $settings = array();

    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }

    public function build()
    {
        return new Client(array('endpoint'=>$this->settings));
    }

} 