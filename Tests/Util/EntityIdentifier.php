<?php

namespace FS\SolrBundle\Tests\Util;


class EntityIdentifier
{
    public static function generate()
    {
        return rand(1, 100000000);
    }
} 