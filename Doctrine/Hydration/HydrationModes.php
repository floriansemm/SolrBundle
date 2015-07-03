<?php

namespace FS\SolrBundle\Doctrine\Hydration;

class HydrationModes
{
    /**
     * use only the values from the index. Ignore not indexed db values.
     */
    const HYDRATE_INDEX = 'index';

    /**
     * use values from the index and db. Resulting entity holds also not indexed values.
     */
    const HYDRATE_DOCTRINE = 'doctrine';
}