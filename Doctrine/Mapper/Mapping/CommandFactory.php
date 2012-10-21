<?php
namespace FS\SolrBundle\Doctrine\Mapper\Mapping;

class CommandFactory {

	/**
	 * @var array
	 */
	private $commands = array();
	
	/**
	 * @param string $command
	 * @throws \RuntimeException
	 * @return AbstractDocumentCommand
	 */
	public function get($command) {
		if (!array_key_exists($command, $this->commands)) {
			throw new \RuntimeException(sprintf('%s is an unknown command', $command));
		}
		
		return $this->commands[$command];
	}
	
	/**
	 * @param AbstractDocumentCommand $command
	 * @param string $commandName
	 */
	public function add(AbstractDocumentCommand $command, $commandName) {
		$this->commands[$commandName] = $command;
	}
}

?>