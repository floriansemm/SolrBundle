<?php

namespace FS\SolrBundle\Tests\Solr\Doctrine;

use FS\SolrBundle\Doctrine\ClassnameResolver;

/**
 * @group resolver
 */
class ClassnameResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function resolveClassnameOfCommonEntity()
    {
        $resolver = $this->getResolverWithOrmConfig('FS\SolrBundle\Tests\Doctrine\Mapper');

        $class = 'FS\SolrBundle\Tests\Doctrine\Mapper\ValidTestEntity';

        $this->assertEquals($class, $resolver->resolveFullQualifiedClassname('FSTest:ValidTestEntity'));
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
}
 