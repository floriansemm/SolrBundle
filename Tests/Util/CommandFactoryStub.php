<?php
namespace FS\SolrBundle\Tests\Util;

use FS\SolrBundle\Doctrine\Mapper\Mapping\MapIdentifierCommand;

use FS\SolrBundle\Doctrine\Mapper\Mapping\MapAllFieldsCommand;
use FS\SolrBundle\Doctrine\Annotation\AnnotationReader;
use FS\SolrBundle\Doctrine\Mapper\Mapping\CommandFactory;

class CommandFactoryStub {
	/**
	 * 
	 * @return \FS\SolrBundle\Doctrine\Mapper\Mapping\CommandFactory
	 */
	public static function getFactoryWithAllMappingCommand() {
		$commandFactory = new CommandFactory();
		$commandFactory->add(new MapAllFieldsCommand(), 'all');
		$commandFactory->add(new MapIdentifierCommand(), 'identifier');
		
		return $commandFactory;		
	}
}

?>