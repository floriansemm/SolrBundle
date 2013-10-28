<?php

namespace FS\SolrBundle\Tests\Solr\Doctrine;

use FS\SolrBundle\Doctrine\ClassnameResolver;

/**
 * @group resolver
 */
class ClassnameResolverTest extends \PHPUnit_Framework_TestCase
{
    const ENTITY_NAMESPACE = 'FS\SolrBundle\Tests\Doctrine\Mapper';
    const UNKNOW_ENTITY_NAMESPACE = 'FS\Unknown';

    /**
     * @test
     */
    public function resolveClassnameOfCommonEntity()
    {
        $resolver = $this->getResolverWithOrmConfig(self::ENTITY_NAMESPACE);

        $expectedClass = 'FS\SolrBundle\Tests\Doctrine\Mapper\ValidTestEntity';

        $this->assertEquals($expectedClass, $resolver->resolveFullQualifiedClassname('FSTest:ValidTestEntity'));
    }

    /**
     * @test
     */
    public function resolveClassnameOfCommonDocument()
    {
        $resolver = $this->getResolverWithOdmConfig(self::ENTITY_NAMESPACE);

        $expectedClass = 'FS\SolrBundle\Tests\Doctrine\Mapper\ValidTestEntity';

        $this->assertEquals($expectedClass, $resolver->resolveFullQualifiedClassname('FSTest:ValidTestEntity'));
    }

    /**
     * @test
     */
    public function resolveClassnameOfCommonEntityWithDifferentConfigurations()
    {
        $resolver = $this->getResolverWithOrmAndOdmConfig(self::ENTITY_NAMESPACE);
        $expectedClass = 'FS\SolrBundle\Tests\Doctrine\Mapper\ValidTestEntity';

        $this->assertEquals($expectedClass, $resolver->resolveFullQualifiedClassname('FSTest:ValidTestEntity'));
    }

    /**
     * @test
     * @expectedException \FS\SolrBundle\Doctrine\ClassnameResolverException
     */
    public function cantResolveClassnameFromUnknowClassWithValidNamespace()
    {
        $resolver = $this->getResolverWithOrmAndOdmConfigBothHasEntity(self::ENTITY_NAMESPACE);

        $resolver->resolveFullQualifiedClassname('FSTest:UnknownEntity');
    }

    /**
     * @test
     * @expectedException \FS\SolrBundle\Doctrine\ClassnameResolverException
     */
    public function cantResolveClassnameIfEntityNamespaceIsUnknown()
    {
        $resolver = $this->getResolverWithOrmConfigPassedInvalidNamespace(self::UNKNOW_ENTITY_NAMESPACE);

        $resolver->resolveFullQualifiedClassname('FStest:entity');
    }

    /**
     * ORM has correct namespace
     *
     * @param string $knownNamespace
     * @return ClassnameResolver
     */
    private function getResolverWithOrmAndOdmConfig($knownNamespace)
    {
        $resolver = new ClassnameResolver();

        $config = $this->getMock('Doctrine\ORM\Configuration', array(), array(), '', false);
        $config->expects($this->once())
            ->method('getEntityNamespace')
            ->will($this->returnValue($knownNamespace));

        $resolver->addOrmConfiguration($config);

        $config = $this->getMock('Doctrine\ODM\MongoDB\Configuration', array(), array(), '', false);
        $config->expects($this->never())
            ->method('getDocumentNamespace')
            ->will($this->returnValue($knownNamespace));

        $resolver->addOdmConfiguration($config);

        return $resolver;
    }

    /**
     * both has a namespace
     *
     * @param string $knownNamespace
     * @return ClassnameResolver
     */
    private function getResolverWithOrmAndOdmConfigBothHasEntity($knownNamespace)
    {
        $resolver = new ClassnameResolver();

        $config = $this->getMock('Doctrine\ORM\Configuration', array(), array(), '', false);
        $config->expects($this->once())
            ->method('getEntityNamespace')
            ->will($this->returnValue($knownNamespace));

        $resolver->addOrmConfiguration($config);

        $config = $this->getMock('Doctrine\ODM\MongoDB\Configuration', array(), array(), '', false);
        $config->expects($this->once())
            ->method('getDocumentNamespace')
            ->will($this->returnValue($knownNamespace));

        $resolver->addOdmConfiguration($config);

        return $resolver;
    }

    private function getResolverWithOrmConfigPassedInvalidNamespace($knownNamespace)
    {
        $config = $this->getMock('Doctrine\ORM\Configuration', array(), array(), '', false);
        $config->expects($this->once())
            ->method('getEntityNamespace')
            ->will($this->throwException(new \Doctrine\ORM\ORMException()));

        $resolver = new ClassnameResolver();
        $resolver->addOrmConfiguration($config);

        return $resolver;
    }

    private function getResolverWithOrmConfig($knownNamespace)
    {
        $config = $this->getMock('Doctrine\ORM\Configuration', array(), array(), '', false);
        $config->expects($this->once())
            ->method('getEntityNamespace')
            ->will($this->returnValue($knownNamespace));

        $resolver = new ClassnameResolver();
        $resolver->addOrmConfiguration($config);

        return $resolver;
    }

    private function getResolverWithOdmConfig($knownNamespace)
    {
        $config = $this->getMock('Doctrine\ODM\MongoDB\Configuration', array(), array(), '', false);
        $config->expects($this->once())
            ->method('getDocumentNamespace')
            ->will($this->returnValue($knownNamespace));

        $resolver = new ClassnameResolver();
        $resolver->addOdmConfiguration($config);

        return $resolver;
    }
}
 