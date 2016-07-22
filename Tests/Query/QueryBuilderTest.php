<?php

namespace FS\SolrBundle\Tests\Query;

use FS\SolrBundle\Doctrine\Mapper\MetaInformation;
use FS\SolrBundle\Query\QueryBuilder;
use FS\SolrBundle\SolrInterface;

class QueryBuilderTest extends \PHPUnit_Framework_TestCase
{
    private $solr;

    public function setUp()
    {
        $this->solr = $this->getMockBuilder(SolrInterface::class)->getMock();
    }

    public function testChristmasReadme()
    {
        $metaInformation = new MetaInformation();
        $metaInformation->setFieldMapping(array(
            'position_p' => 'position',
            'santa-beard-exists_b' => 'santa-beard-exists',
            'santa-beard-lenght_f' => 'santa-beard-lenght',
            'santa-beard-color_s' => 'santa-beard-color',
            'good-actions_i' => 'good-actions',
            'gift-name_s' => 'gift-name',
            'gift-type_s' => 'gift-type',
            'gift-received_s' => 'gift-received',
            'chimney_s' => 'chimney',
            'date_dt' => 'date'
        ));

        $builder = new QueryBuilder($this->solr, $metaInformation);

        $nearNorthPole  = Criteria::where('position')->nearCircle(38.116181, -86.929463, 100.5);
        self::assertEquals("{!bbox pt=38.116181,\\-86.929463 sfield=position d=100.5}", $nearNorthPole->getQuery());

        $santaClaus = Criteria::where('santa-name')->contains(['Noel', 'Claus', 'Natale', 'Baba', 'Nicolas'])
            ->andWhere('santa-beard-exists')->is(true)
            ->andWhere('santa-beard-lenght')->between(5.5, 10.0)
            ->andWhere('santa-beard-color')->startsWith('whi')->endsWith('te')
            ->andWhere($nearNorthPole);

        self::assertEquals("santa-name:(*Noel* *Claus* *Natale* *Baba* *Nicolas*) AND santa-beard-exists:true AND santa-beard-lenght:[5.5 TO 10] AND santa-beard-color:(whi* *te) AND {!bbox pt=38.116181,\\-86.929463 sfield=position d=100.5}", $santaClaus->getQuery());

        $goodPeople = Criteria::where('good-actions')->greaterThanEqual(10)
            ->orWhere('bad-actions')->lessThanEqual(5);

        self::assertEquals('good-actions:[10 TO *] OR bad-actions:[* TO 5]', $goodPeople->getQuery());

        $gifts = Criteria::where('gift-name')->sloppy('LED TV GoPro Oculus Tablet Laptop', 2)
            ->andWhere('gift-type')->fuzzy('information', 0.4)->startsWith('tech')
            ->andWhere('__query__')->expression('{!dismax qf=myfield}how now brown cow');

        self::assertEquals('gift-name:"LED TV GoPro Oculus Tablet Laptop"~2 AND gift-type:(information~0.4 tech*) AND __query__:{!dismax qf=myfield}how now brown cow', $gifts->getQuery());

        $christmas = new DateTime('2016-12-25');
        $contributors = ['Christoph', 'Philipp', 'Francisco', 'Fabio'];
        $giftReceivers  = Criteria::where('gift-received')->is(null)
            ->andWhere('chimney')->isNotNull()
            ->andWhere('date')->is($christmas)->greaterThanEqual(new \Datetime('1970-01-01'))
            ->andWhere($santaClaus)
            ->andWhere($gifts)
            ->andWhere(
                Criteria::where('name')->in($contributors)->boost(2.0)
                    ->orWhere($goodPeople)
            );

        self::assertEquals("-gift-received:[* TO *] AND chimney:[* TO *] AND date:(2016\\-12\\-25T00\\:00\\:00Z [1970\\-01\\-01T00\\:00\\:00Z TO *]) AND (santa-name:(*Noel* *Claus* *Natale* *Baba* *Nicolas*) AND santa-beard-exists:true AND santa-beard-lenght:[5.5 TO 10] AND santa-beard-color:(whi* *te) AND {!bbox pt=38.116181,\\-86.929463 sfield=position d=100.5}) AND (gift-name:\"LED TV GoPro Oculus Tablet Laptop\"~2 AND gift-type:(information~0.4 tech*) AND __query__:{!dismax qf=myfield}how now brown cow) AND (name:(Christoph Philipp Francisco Fabio)^2.0 OR (good-actions:[10 TO *] OR bad-actions:[* TO 5]))", $giftReceivers->getQuery());
    }
}