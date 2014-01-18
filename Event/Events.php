<?php

namespace FS\SolrBundle\Event;

final class Events
{
    const PRE_INSERT = 'pre_insert';
    const POST_INSERT = 'post_insert';

    const PRE_UPDATE = 'pre_update';
    const POST_UPDATE = 'post_update';

    const PRE_DELETE = 'pre_delete';
    const POST_DELETE = 'post_delete';

    const PRE_CLEAR_INDEX = 'pre_clear_index';
    const POST_CLEAR_INDEX = 'post_clear_index';

    const ERROR = 'error';
} 