<?php
namespace FS\SolrBundle\Tests\Doctrine\Annotation\Entities;

use FS\SolrBundle\Doctrine\Annotation as Solr;

/**
 * 
 * @Solr\Document
 * @Solr\Boost("aaaa")
 */
class ValidTestEntityWithInvalidBoost {
	
	/**
	 * @Solr\Id
	 */
	private $id;
}

?>