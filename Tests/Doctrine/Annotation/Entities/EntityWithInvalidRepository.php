<?php
namespace FS\SolrBundle\Tests\Doctrine\Annotation\Entities;

use FS\SolrBundle\Doctrine\Annotation as Solr;

/**
 * 
 * @Solr\Document(repository="FS\SolrBundle\Tests\Doctrine\Annotation\Entities\InvalidEntityRepository")
 *
 */
class EntityWithInvalidRepository {

}

?>