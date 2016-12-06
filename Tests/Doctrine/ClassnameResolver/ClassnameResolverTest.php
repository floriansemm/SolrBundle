<?php

namespace FS\SolrBundle\Tests\Solr\Doctrine;

use FS\SolrBundle\Doctrine\ClassnameResolver\ClassnameResolver;
use FS\SolrBundle\Doctrine\ClassnameResolver\KnownNamespaceAliases;

/**
 * @group resolver
 */
class ClassnameResolverTest extends \PHPUnit_Framework_TestCase
{
    const ENTITY_NAMESPACE = 'FS\SolrBundle\Tests\Doctrine\Mapper';
    const UNKNOW_ENTITY_NAMESPACE = 'FS\Unknown';

    private $knownAliases;

    public function setUp()
    {
        $this->knownAliases = $this->createMock(KnownNamespaceAliases::class);
    }

    /**
     * @test
     */
    public function resolveClassnameOfCommonEntity()
    {
        $resolver = $this->getResolverWithKnowNamespace(self::ENTITY_NAMESPACE);

        $expectedClass = 'FS\SolrBundle\Tests\Doctrine\Mapper\ValidTestEntity';

        $this->assertEquals($expectedClass, $resolver->resolveFullQualifiedClassname('FSTest:ValidTestEntity'));
    }

    /**
     * @test
     * @expectedException \FS\SolrBundle\Doctrine\ClassnameResolver\ClassnameResolverException
     */
    public function cantResolveClassnameFromUnknowClassWithValidNamespace()
    {
        $resolver = $this->getResolverWithOrmAndOdmConfigBothHasEntity(self::ENTITY_NAMESPACE);

        $resolver->resolveFullQualifiedClassname('FSTest:UnknownEntity');
    }

    /**
     * @test
     * @expectedException \FS\SolrBundle\Doctrine\ClassnameResolver\ClassnameResolverException
     */
    public function cantResolveClassnameIfEntityNamespaceIsUnknown()
    {
        $resolver = $this->getResolverWithOrmConfigPassedInvalidNamespace(self::UNKNOW_ENTITY_NAMESPACE);

        $resolver->resolveFullQualifiedClassname('FStest:entity');
    }

    /**
     * both has a namespace
     *
     * @param string $knownNamespace
     * @return ClassnameResolver
     */
    private function getResolverWithOrmAndOdmConfigBothHasEntity($knownNamespace)
    {
        $this->knownAliases->expects($this->once())
            ->method('isKnownNamespaceAlias')
            ->will($this->returnValue(true));

        $this->knownAliases->expects($this->once())
            ->method('getFullyQualifiedNamespace')
            ->will($this->returnValue($knownNamespace));

        $resolver = new ClassnameResolver($this->knownAliases);

        return $resolver;
    }

    private function getResolverWithOrmConfigPassedInvalidNamespace($knownNamespace)
    {
        $this->knownAliases->expects($this->once())
            ->method('isKnownNamespaceAlias')
            ->will($this->returnValue(false));

        $this->knownAliases->expects($this->once())
            ->method('getAllNamespaceAliases')
            ->will($this->returnValue(array('FSTest')));

        $resolver = new ClassnameResolver($this->knownAliases);

        return $resolver;
    }

    private function getResolverWithKnowNamespace($knownNamespace)
    {
        $this->knownAliases->expects($this->once())
            ->method('isKnownNamespaceAlias')
            ->will($this->returnValue(true));

        $this->knownAliases->expects($this->once())
            ->method('getFullyQualifiedNamespace')
            ->will($this->returnValue($knownNamespace));

        $resolver = new ClassnameResolver($this->knownAliases);

        return $resolver;
    }
}
 