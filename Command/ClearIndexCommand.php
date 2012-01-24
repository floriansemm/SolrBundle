<?php
namespace FS\SolrBundle\Command;

use FS\SolrBundle\SolrQuery;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ClearIndexCommand extends ContainerAwareCommand {
	protected function configure() {
		$this
		->setName('solr:index:clear')
		->setDescription('Clear the whole index');
	}
	
	protected function execute(InputInterface $input, OutputInterface $output) {
		$solr = $this->getContainer()->get('solr');		
		$solr->clearIndex();
	}
}

?>