<?php
namespace FS\SolrBundle\Tests\Doctrine\Annotation\Entities;

use FS\SolrBundle\Doctrine\Annotation as Solr;

/**
 *
 * @Solr\Document(index="index0")
 * @Solr\SynchronizationFilter(callback="shouldBeIndex")
 */
class ValidTestEntityFiltered
{
    /**
     * @Solr\Id()
     *
     * @var int
     */
    private $id;

    private $shouldBeIndexedWasCalled = false;

    public $shouldIndex = false;

    public function shouldBeIndex()
    {
        $this->shouldBeIndexedWasCalled = true;

        return $this->shouldIndex;
    }

    public function getShouldBeIndexedWasCalled()
    {
        return $this->shouldBeIndexedWasCalled;
    }
}

