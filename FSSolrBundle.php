<?php

namespace FS\SolrBundle;

use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use FS\SolrBundle\DependencyInjection\Compiler\AddCreateDocumentCommandPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class FSSolrBundle extends Bundle
{
	public function build(ContainerBuilder $container) {
		parent::build($container);
	
		$container->addCompilerPass(new AddCreateDocumentCommandPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION);
	}	
}
