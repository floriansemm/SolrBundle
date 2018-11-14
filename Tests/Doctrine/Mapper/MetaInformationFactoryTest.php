<?php
namespace FS\SolrBundle\Tests\Doctrine\Mapper;

use FS\SolrBundle\Doctrine\Annotation\AnnotationReader;
use FS\SolrBundle\Doctrine\Annotation\Field;
use FS\SolrBundle\Doctrine\ClassnameResolver\ClassnameResolver;
use FS\SolrBundle\Doctrine\ClassnameResolver\ClassnameResolverException;
use FS\SolrBundle\Doctrine\Mapper\MetaInformationFactory;
use FS\SolrBundle\Doctrine\Mapper\MetaInformation;
use FS\SolrBundle\Doctrine\Mapper\MetaInformationInterface;
use FS\SolrBundle\Tests\Fixtures\EntityNestedProperty;
use FS\SolrBundle\Tests\Fixtures\NestedEntity;
use FS\SolrBundle\Tests\Fixtures\NotIndexedEntity;
use FS\SolrBundle\Tests\Fixtures\ValidOdmTestDocument;
use FS\SolrBundle\Tests\Fixtures\ValidTestEntity;

/**
 *
 * @group mapper
 */
class MetaInformationFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AnnotationReader
     */
    private $reader;

    public function setUp()
    {
        $this->reader = new AnnotationReader(new \Doctrine\Common\Annotations\AnnotationReader());
    }

    private function getClassnameResolver($namespace)
    {
        $doctrineConfiguration = $this->createMock(ClassnameResolver::class);
        $doctrineConfiguration->expects($this->any())
            ->method('resolveFullQualifiedClassname')
            ->will($this->returnValue($namespace));

        return $doctrineConfiguration;
    }

    private function getClassnameResolverCouldNotResolveClassname()
    {
        $doctrineConfiguration = $this->createMock(ClassnameResolver::class);
        $doctrineConfiguration->expects($this->any())
            ->method('resolveFullQualifiedClassname')
            ->will($this->throwException(new ClassnameResolverException('could not resolve classname for entity')));

        return $doctrineConfiguration;
    }

    public function testLoadInformation_ShouldLoadAll()
    {
        $testEntity = new ValidTestEntity();
        $expectedClassName = get_class($testEntity);

        $expectedDocumentName = 'validtestentity';

        $classnameResolver = $this->getClassnameResolver(ValidTestEntity::class);

        $factory = new MetaInformationFactory($this->reader);
        $factory->setClassnameResolver($classnameResolver);
        $actual = $factory->loadInformation('FSBlogBundle:ValidTestEntity');

        $this->assertTrue($actual instanceof MetaInformation);
        $this->assertEquals($expectedClassName, $actual->getClassName(), 'wrong classname');
        $this->assertEquals($expectedDocumentName, $actual->getDocumentName(), 'wrong documentname');
        $this->assertEquals(5, count($actual->getFields()), '5 fields are set');
        $this->assertEquals(6, count($actual->getFieldMapping()), '5 fields are mapped');
    }

    public function testLoadInformation_LoadInformationFromObject()
    {
        $testEntity = new ValidTestEntity();
        $expectedClassName = get_class($testEntity);

        $expectedDocumentName = 'validtestentity';

        $doctrineConfiguration = $this->getClassnameResolver(ValidTestEntity::class);

        $factory = new MetaInformationFactory($this->reader);
        $factory->setClassnameResolver($doctrineConfiguration);
        $actual = $factory->loadInformation($testEntity);

        $this->assertTrue($actual instanceof MetaInformation);
        $this->assertEquals($expectedClassName, $actual->getClassName(), 'wrong classname');
        $this->assertEquals($expectedDocumentName, $actual->getDocumentName(), 'wrong documentname');
        $this->assertEquals(5, count($actual->getFields()), '5 fields are mapped');

        $this->assertTrue($actual->hasField('title'), 'field should be able to located by field-name');
        $this->assertTrue($actual->hasField('text_t'), 'field should be able to located by field-name with suffix');

        $this->assertTrue($actual->getField('title') instanceof Field);
    }

    /**
     * @expectedException \FS\SolrBundle\Doctrine\Mapper\SolrMappingException
     * @expectedExceptionMessage no declaration for document found in entity
     */
    public function testLoadInformation_EntityHasNoDocumentDeclaration_ShouldThrowException()
    {
        $doctrineConfiguration = $this->getClassnameResolver(NotIndexedEntity::class);

        $factory = new MetaInformationFactory($this->reader);
        $factory->setClassnameResolver($doctrineConfiguration);
        $factory->loadInformation('FSBlogBundle:NotIndexedEntity');
    }

    /**
     * @expectedException \FS\SolrBundle\Doctrine\ClassnameResolver\ClassnameResolverException
     * @expectedExceptionMessage could not resolve classname for entity
     */
    public function testLoadInformation_EntityDoesNoExists()
    {
        $doctrineConfiguration = $this->getClassnameResolverCouldNotResolveClassname();

        $factory = new MetaInformationFactory($this->reader);
        $factory->setClassnameResolver($doctrineConfiguration);
        $factory->loadInformation('FSBlogBundle:UnknownEntity');
    }

    public function testLoadInformation_FromObject()
    {
        $doctrineConfiguration = $this->getClassnameResolver(ValidTestEntity::class);

        $factory = new MetaInformationFactory($this->reader);
        $factory->setClassnameResolver($doctrineConfiguration);

        $testEntity = new ValidTestEntity();
        $informations = $factory->loadInformation($testEntity);

        $expected = get_class($testEntity);
        $this->assertEquals($expected, $informations->getClassName(), 'class from object not discovered');
    }

    public function testLoadInformation_FromFullClassname()
    {
        $doctrineConfiguration = $this->getClassnameResolver(ValidTestEntity::class);

        $factory = new MetaInformationFactory($this->reader);
        $factory->setClassnameResolver($doctrineConfiguration);

        $entityClassname = get_class(new ValidTestEntity());
        $informations = $factory->loadInformation($entityClassname);

        $expected = $entityClassname;
        $this->assertEquals($expected, $informations->getClassName(), 'class from fullclassname not discovered');
    }

    /**
     * @test
     */
    public function determineDoctrineMapperTypeFromEntity()
    {
        $factory = new MetaInformationFactory($this->reader);
        $metainformation = $factory->loadInformation(new ValidTestEntity());

        $this->assertEquals(MetaInformationInterface::DOCTRINE_MAPPER_TYPE_RELATIONAL, $metainformation->getDoctrineMapperType());
    }

    /**
     * @test
     */
    public function determineDoctrineMapperTypeFromDocument()
    {
        $factory = new MetaInformationFactory($this->reader);
        $metainformation = $factory->loadInformation(new ValidOdmTestDocument());

        $this->assertEquals(MetaInformationInterface::DOCTRINE_MAPPER_TYPE_DOCUMENT, $metainformation->getDoctrineMapperType());
    }

    /**
     * @test
     */
    public function useCachedEntityInstanceIfItIsSet()
    {
        $factory = new MetaInformationFactory($this->reader);
        $metainformation1 = $factory->loadInformation(new ValidTestEntity());
        $metainformation2 = $factory->loadInformation(new ValidTestEntity());

        $this->assertEquals($metainformation1->getEntity(), $metainformation2->getEntity());
    }

    /**
     * @test
     */
    public function includeNestedFieldsInFieldmapping()
    {
        $entity = new EntityNestedProperty();

        $nested1 = new NestedEntity();
        $nested2 = new NestedEntity();
        $entity->setCollection([$nested1, $nested2]);

        $factory = new MetaInformationFactory($this->reader);
        $metainformation = $factory->loadInformation($entity);

        $this->assertArrayNotHasKey('collection', $metainformation->getFieldMapping());
        $this->assertArrayHasKey('collection.id', $metainformation->getFieldMapping());
        $this->assertArrayHasKey('collection.name_t', $metainformation->getFieldMapping());


    }
}

