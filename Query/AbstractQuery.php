<?php
namespace FS\SolrBundle\Query;

abstract class AbstractQuery {
	
	/**
	 * @return string 
	 */
	abstract public function getQueryString();
}
