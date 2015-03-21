<?php


namespace FS\SolrBundle\Tests;


use FS\SolrBundle\Tests\Util\CommandFactoryStub;
use FS\SolrBundle\Tests\Util\MetaTestInformationFactory;

abstract class AbstractSolrTest extends \PHPUnit_Framework_TestCase
{

    protected $metaFactory = null;
    protected $config = null;
    protected $commandFactory = null;
    protected $eventDispatcher = null;
    protected $mapper = null;
    protected $solrClientFake = null;

    public function setUp()
    {
        $this->metaFactory = $metaFactory = $this->getMock(
            'FS\SolrBundle\Doctrine\Mapper\MetaInformationFactory',
            array(),
            array(),
            '',
            false
        );
        $this->config = $this->getMock('FS\SolrBundle\SolrConnection', array(), array(), '', false);
        $this->commandFactory = CommandFactoryStub::getFactoryWithAllMappingCommand();
        $this->eventDispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcher', array(), array(), '', false);
        $this->mapper = $this->getMock('FS\SolrBundle\Doctrine\Mapper\EntityMapper', array(), array(), '', false);

        $this->solrClientFake = $this->getMock('Solarium\Client', array(), array(), '', false);
    }

    protected function assertUpdateQueryExecuted()
    {
        $updateQuery = $this->getMock('Solarium\QueryType\Update\Query\Query', array(), array(), '', false);
        $updateQuery->expects($this->once())
            ->method('addDocument');

        $updateQuery->expects($this->once())
            ->method('addCommit');

        $this->solrClientFake
            ->expects($this->once())
            ->method('createUpdate')
        ->will($this->returnValue($updateQuery));
    }

    protected function assertUpdateQueryWasNotExecuted()
    {
        $updateQuery = $this->getMock('Solarium\QueryType\Update\Query\Query', array(), array(), '', false);
        $updateQuery->expects($this->never())
            ->method('addDocument');

        $updateQuery->expects($this->never())
            ->method('addCommit');

        $this->solrClientFake
            ->expects($this->never())
            ->method('createUpdate');
    }

    protected function assertDeleteQueryWasExecuted()
    {
        $deleteQuery = $this->getMock('Solarium\QueryType\Update\Query\Query', array(), array(), '', false);
        $deleteQuery->expects($this->once())
            ->method('addDeleteQuery')
        ->with($this->isType('string'));

        $deleteQuery->expects($this->once())
            ->method('addCommit');

        $this->solrClientFake
            ->expects($this->once())
            ->method('createUpdate')
        ->will($this->returnValue($deleteQuery));

        $this->solrClientFake
            ->expects($this->once())
            ->method('update')
        ->with($deleteQuery);
    }

    protected function setupMetaFactoryLoadOneCompleteInformation($metaInformation = null)
    {
        if (null === $metaInformation) {
            $metaInformation = MetaTestInformationFactory::getMetaInformation();
        }

        $this->metaFactory->expects($this->once())
            ->method('loadInformation')
        ->will($this->returnValue($metaInformation));
    }

    protected function assertQueryWasExecuted($data = array())
    {
        $selectQuery = $this->getMock('Solarium\QueryType\Select\Query\Query', array(), array(), '', false);
        $selectQuery->expects($this->once())
            ->method('setQuery');

        $queryResult = new ResultFake($data);

        $this->solrClientFake
            ->expects($this->once())
            ->method('createSelect')
        ->will($this->returnValue($selectQuery));

        $this->solrClientFake
            ->expects($this->once())
            ->method('select')
        ->with($selectQuery)
        ->will($this->returnValue($queryResult));
    }

    protected function mapOneDocument()
    {
        $this->mapper->expects($this->once())
            ->method('toDocument')
        ->will($this->returnValue($this->getMock('Solarium\QueryType\Update\Query\Document\DocumentInterface')));
    }
}