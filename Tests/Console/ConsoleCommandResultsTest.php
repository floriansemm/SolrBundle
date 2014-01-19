<?php

namespace FS\Console;


use FS\SolrBundle\Console\CommandResult;
use FS\SolrBundle\Console\ConsoleCommandResults;

class ConsoleCommandResultsTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function succeedResultsShouldRemoveWhenFoundInErrored()
    {
        $results = new ConsoleCommandResults();
        $results->success(new CommandResult(1, 'a class'));
        $results->success(new CommandResult(2, 'a class'));
        $results->success(new CommandResult(3, 'a class'));

        $results->error(new CommandResult(2, 'a class'));

        $this->assertEquals(2, $results->getSucceed());

        $this->assertArrayHasKey(2, $results->getErrors());

        $this->assertArrayHasKey(1, $results->getSuccess());
        $this->assertArrayHasKey(3, $results->getSuccess());
        $this->assertArrayNotHasKey(2, $results->getSuccess());
    }
}
 