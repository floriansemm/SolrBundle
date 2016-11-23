<?php


namespace FS\SolrBundle\Tests;


use FS\SolrBundle\Doctrine\Mapper\EntityMapperInterface;
use FS\SolrBundle\Doctrine\Mapper\MetaInformationFactory;
use FS\SolrBundle\Tests\Util\MetaTestInformationFactory;
use Solarium\Client;
use Solarium\QueryType\Update\Query\Document\DocumentInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Solarium\QueryType\Update\Query\Query as UpdateQuery;
use Solarium\QueryType\Select\Query\Query as SelectQuery;

abstract class AbstractSolrTest extends \PHPUnit_Framework_TestCase
{

    protected $metaFactory = null;
    protected $eventDispatcher = null;
    protected $mapper = null;
    protected $solrClientFake = null;

    public function setUp()
    {
        $this->metaFactory = $metaFactory = $this->createMock(MetaInformationFactory::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->mapper = $this->getMockBuilder(EntityMapperInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(array('setMappingCommand', 'toDocument', 'toEntity', 'setHydrationMode'))
            ->getMock();

        $this->solrClientFake = $this->createMock(Client::class);
    }

    protected function assertUpdateQueryExecuted($index = null)
    {
        $updateQuery = $this->createMock(UpdateQuery::class);
        $updateQuery->expects($this->once())
            ->method('addDocument');

        $updateQuery->expects($this->once())
            ->method('addCommit');

        $this->solrClientFake
            ->expects($this->once())
            ->method('createUpdate')
            ->will($this->returnValue($updateQuery));

        $this->solrClientFake
            ->expects($this->once())
            ->method('update')
            ->with($updateQuery, $index);

        return $updateQuery;
    }

    protected function assertUpdateQueryWasNotExecuted()
    {
        $updateQuery = $this->createMock(UpdateQuery::class);
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
        $deleteQuery = $this->createMock(UpdateQuery::class);
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

    protected function assertQueryWasExecuted($data = array(), $index)
    {
        $selectQuery = $this->createMock(SelectQuery::class);
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
            ->will($this->returnValue($queryResult));
    }

    protected function mapOneDocument()
    {
        $this->mapper->expects($this->once())
            ->method('toDocument')
            ->will($this->returnValue($this->createMock(DocumentInterface::class)));
    }
}