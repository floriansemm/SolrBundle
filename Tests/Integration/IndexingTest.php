<?php

namespace FS\SolrBundle\Tests\Integration;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use FS\SolrBundle\Client\Solarium\SolariumClientBuilder;
use FS\SolrBundle\Doctrine\Annotation\AnnotationReader;
use FS\SolrBundle\Doctrine\Hydration\DoctrineHydrator;
use FS\SolrBundle\Doctrine\Hydration\IndexHydrator;
use FS\SolrBundle\Doctrine\Hydration\ValueHydrator;
use FS\SolrBundle\Doctrine\Mapper\EntityMapper;
use FS\SolrBundle\Doctrine\Mapper\MetaInformationFactory;
use FS\SolrBundle\Doctrine\ORM\Listener\EntityIndexerSubscriber;
use FS\SolrBundle\Solr;
use FS\SolrBundle\Tests\Integration\Entity\Category;
use FS\SolrBundle\Tests\Integration\Entity\Post;
use FS\SolrBundle\Tests\Integration\Entity\Tag;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\DistributionBundle\Configurator\Step\DoctrineStep;
use Solarium\Client;
use Solarium\QueryType\Ping\Query;

class IndexingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Solr
     */
    private $solr;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var EntityIndexerSubscriber
     */
    private $doctrineSubscriber;

    /**
     * @var EventDispatcherFake
     */
    private $eventDispatcher;

    protected function setUp()
    {
        $this->eventDispatcher = new EventDispatcherFake();
        $this->client = $this->setupSolrClient();

        try {
            $this->client->ping(new Query());
        } catch (\Exception $e) {
            $this->markTestSkipped('solr is not running on localhost:8983');
            return;
        }

        $metainformationFactory = new MetaInformationFactory(new AnnotationReader(new \Doctrine\Common\Annotations\AnnotationReader()));
        $logger = $this->createMock(LoggerInterface::class);

        $this->solr = new Solr(
            $this->client,
            $this->eventDispatcher,
            $metainformationFactory,
            new EntityMapper(
                new DoctrineHydrator(new ValueHydrator()),
                new IndexHydrator(new ValueHydrator()),
                $metainformationFactory
            )
        );

        $this->solr->clearIndex();

        $this->doctrineSubscriber = new EntityIndexerSubscriber($this->solr, $metainformationFactory, $logger);
    }

    /**
     * Solarium Client with two cores (core0, core1)
     *
     * @return Client
     */
    private function setupSolrClient()
    {
        $config = array(
            'core0' => array(
                'host' => 'localhost',
                'port' => 8983,
                'path' => '/solr/core0',
            )
        );

        $builder = new SolariumClientBuilder($config, $this->eventDispatcher);
        $solrClient = $builder->build();

        return $solrClient;
    }

    /**
     * @test
     */
    public function indexSingleEntity()
    {
        $currentDate = date('c') . 'Z';

        $post = new Post();
        $post->setId(1);
        $post->setTitle('indexSingleEntity');
        $post->setCreated($currentDate);
        $post->setMultivalues(['foo', 'bar']);

        $objectManager = $this->createMock(EntityManagerInterface::class);

        $lifecycleEventArgs = new LifecycleEventArgs($post, $objectManager);

        $this->doctrineSubscriber->postPersist($lifecycleEventArgs);

        $this->doctrineSubscriber->postFlush(new PostFlushEventArgs($objectManager));

        $events = $this->eventDispatcher->getEvents();

        $expectedRequest = '<update><add><doc><field name="id">post_1</field><field name="title_s">indexSingleEntity</field><field name="created_dt">' . $currentDate . '</field><field name="multivalues_ss" update="remove">foo</field><field name="multivalues_ss" update="remove">bar</field></doc></add><commit/></update>';

        $this->assertEquals($expectedRequest, $events['solarium.core.preExecuteRequest']->getRequest()->getRawData());
    }

    /**
     * @test
     */
    public function deleteEntityWithNested()
    {
        $post = new Post();
        $post->setId(1);
        $post->setTitle('deleteEntityWithOneToOne');

        $category = new Category();
        $category->setId(1);
        $category->setTitle('deleteEntityWithOneToOne category');

        $post->setCategory($category);

        $objectManager = $this->createMock(EntityManagerInterface::class);

        $this->doctrineSubscriber->postPersist(new LifecycleEventArgs($post, $objectManager));
        $this->doctrineSubscriber->postFlush(new PostFlushEventArgs($objectManager));

        $this->assertEntityExists('deleteEntityWithOneToOne', 'deleteEntityWithOneToOne category');

        $this->doctrineSubscriber->preRemove(new LifecycleEventArgs($category, $objectManager));
        $this->doctrineSubscriber->preRemove(new LifecycleEventArgs($post, $objectManager));
        $this->doctrineSubscriber->postFlush(new PostFlushEventArgs($objectManager));

        $this->assertEntityNotExists('deleteEntityWithOneToOne', 'deleteEntityWithOneToOne category');
    }

    private function assertEntityExists($postName, $categoryName)
    {
        $query = $this->client->createSelect();
        $query->setQuery('title_s:' . $postName);

        $result = $this->client->execute($query);

        $this->assertEquals(1, $result->getData()['response']['numFound']);

        $query->setQuery('title_s:"'. $categoryName .'"');

        $result = $this->client->execute($query);

        $this->assertEquals(1, $result->getData()['response']['numFound']);
    }

    private function assertEntityNotExists($postName, $categoryName)
    {
        $query = $this->client->createSelect();
        $query->setQuery('title_s:' . $postName);

        $result = $this->client->execute($query);

        $this->assertEquals(0, $result->getData()['response']['numFound']);

        $query->setQuery('title_s:"'. $categoryName .'"');

        $result = $this->client->execute($query);

        $this->assertEquals(0, $result->getData()['response']['numFound']);
    }

    /**
     * @test
     */
    public function indexEntityWithOneToOne()
    {
        $post = new Post();
        $post->setId(1);
        $post->setTitle('indexEntityWithOneToOne');

        $category = new Category();
        $category->setId(1);
        $category->setTitle('indexEntityWithOneToOne category');

        $post->setCategory($category);

        $objectManager = $this->createMock(EntityManagerInterface::class);

        $lifecycleEventArgs = new LifecycleEventArgs($post, $objectManager);

        $this->doctrineSubscriber->postPersist($lifecycleEventArgs);

        $this->doctrineSubscriber->postFlush(new PostFlushEventArgs($objectManager));

        $events = $this->eventDispatcher->getEvents();

        $expectedRequest = '<update><add><doc><field name="id">post_1</field><field name="title_s">indexEntityWithOneToOne</field><doc><field name="id">category_1</field><field name="title_s">indexEntityWithOneToOne category</field></doc></doc></add><commit/></update>';

        $this->assertEquals($expectedRequest, $events['solarium.core.preExecuteRequest']->getRequest()->getRawData());

        $this->assertEntityExists('indexEntityWithOneToOne', 'indexEntityWithOneToOne category');
    }

    /**
     * @test
     */
    public function indexEntityWithOneToMany()
    {
        $post = new Post();
        $post->setId(1);
        $post->setTitle('indexEntityWithOneToMany');

        $tag1 = new Tag('tag indexEntityWithOneToMany 1');
        $tag1->setId(1);
        $tag2 = new Tag('tag indexEntityWithOneToMany 2');
        $tag2->setId(2);

        $post->setTags([$tag1, $tag2]);

        $objectManager = $this->createMock(EntityManagerInterface::class);


        $this->doctrineSubscriber->postPersist(new LifecycleEventArgs($tag1, $objectManager));
        $this->doctrineSubscriber->postPersist(new LifecycleEventArgs($tag2, $objectManager));
        $this->doctrineSubscriber->postPersist(new LifecycleEventArgs($post, $objectManager));

        $this->doctrineSubscriber->postFlush(new PostFlushEventArgs($objectManager));

        $events = $this->eventDispatcher->getEvents();

        $expectedRequest = '<update><add><doc><field name="id">post_1</field><field name="title_s">indexEntityWithOneToMany</field><doc><field name="id">tag_1</field><field name="name_s">tag indexEntityWithOneToMany 1</field></doc><doc><field name="id">tag_2</field><field name="name_s">tag indexEntityWithOneToMany 2</field></doc></doc></add><commit/></update>';

        $this->assertEquals($expectedRequest, $events['solarium.core.preExecuteRequest']->getRequest()->getRawData());
    }
}