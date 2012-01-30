<?php
namespace FS\SolrBundle\Repository;

interface RepositoryInterface {
	public function findBy(array $args);
	
	public function find($id);
	
	public function findOneBy(array $args);

	public function findAll();
}

?>