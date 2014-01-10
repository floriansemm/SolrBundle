<?php

namespace FS\SolrBundle\Tests\Util;


class EntityIdentifier
{
    public function generate()
    {
        return rand(1, 15);
    }
} 