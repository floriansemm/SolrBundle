<?php
namespace FS\SolrBundle\Doctrine\Mapper\Command;

class CommandFactory {
	private $commands = array();
	
	public function get($command) {
		if (!array_key_exists($command, $this->commands)) {
			throw new \RuntimeException(sprintf('%s is an unknown command', $command));
		}
		
		return $this->commands[$command];
	}
	
	public function add(CreateDocumentCommandInterface $command, $commandName) {
		$this->commands[$commandName] = $command;
	}
}

?>