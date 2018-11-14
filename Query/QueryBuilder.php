<?php

namespace FS\SolrBundle\Query;

use FS\SolrBundle\Doctrine\Mapper\MetaInformation;
use FS\SolrBundle\Doctrine\Mapper\MetaInformationInterface;
use FS\SolrBundle\Query\Exception\UnknownFieldException;
use FS\SolrBundle\SolrInterface;
use Minimalcode\Search\Criteria;

class QueryBuilder implements QueryBuilderInterface
{
    /**
     * @var SolrInterface
     */
    private $solr;

    /**
     * @var MetaInformation
     */
    private $metaInformation;

    /**
     * @var Criteria
     */
    private $criteria;

    /**
     * @param SolrInterface            $solr
     * @param MetaInformationInterface $metaInformation
     */
    public function __construct(SolrInterface $solr, MetaInformationInterface $metaInformation)
    {
        $this->solr = $solr;
        $this->metaInformation = $metaInformation;
    }

    /**
     * {@inheritdoc}
     */
    public function where($field)
    {
        $solrField = $this->metaInformation->getField($field);
        if ($solrField === null) {
            throw new UnknownFieldException(sprintf('Field %s does not exists', $field));
        }

        $fieldName = $solrField->getNameWithAlias();

        $this->criteria = Criteria::where($fieldName);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function andWhere($field)
    {
        if ($field instanceof QueryBuilder) {
            $this->criteria = $this->criteria->andWhere($field->getCriteria());

            return $this;
        }

        $solrField = $this->metaInformation->getField($field);
        if ($solrField === null) {
            throw new UnknownFieldException(sprintf('Field %s does not exists', $field));
        }

        $fieldName = $solrField->getNameWithAlias();

        $this->criteria = $this->criteria->andWhere($fieldName);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function orWhere($field)
    {
        if ($field instanceof QueryBuilder) {
            $this->criteria = $this->criteria->orWhere($field->getCriteria());

            return $this;
        }

        $solrField = $this->metaInformation->getField($field);
        if ($solrField === null) {
            throw new UnknownFieldException(sprintf('Field %s does not exists', $field));
        }

        $fieldName = $solrField->getNameWithAlias();

        $this->criteria = $this->criteria->orWhere($fieldName);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function is($value)
    {
        $this->criteria = $this->criteria->is($value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function between($lowerBound, $upperBound, $includeLowerBound = true, $includeUpperBound = true)
    {
        $this->criteria = $this->criteria->between($lowerBound, $upperBound, $includeLowerBound, $includeUpperBound);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function in(array $values)
    {
        $this->criteria = $this->criteria->in($values);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function withinCircle($latitude, $longitude, $distance)
    {
        $this->criteria = $this->criteria->withinCircle($latitude, $longitude, $distance);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function withinBox($startLatitude, $startLongitude, $endLatitude, $endLongitude)
    {
        $this->criteria = $this->criteria->withinBox($startLatitude, $startLongitude, $endLatitude, $endLongitude);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function nearCircle($latitude, $longitude, $distance)
    {
        $this->criteria = $this->criteria->nearCircle($latitude, $longitude, $distance);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isNull()
    {
        $this->criteria = $this->criteria->isNull();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isNotNull()
    {
        $this->criteria = $this->criteria->isNotNull();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function contains($value)
    {
        $this->criteria = $this->criteria->contains($value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function startsWith($prefix)
    {
        $this->criteria = $this->criteria->startsWith($prefix);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function endsWith($postfix)
    {
        $this->criteria = $this->criteria->endsWith($postfix);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function not()
    {
        $this->criteria = $this->criteria->not();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function notOperator()
    {
        $this->criteria = $this->criteria->notOperator();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function fuzzy($value, $levenshteinDistance = null)
    {
        $this->criteria = $this->criteria->fuzzy($value, $levenshteinDistance);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function sloppy($phrase, $distance)
    {
        $this->criteria = $this->criteria->sloppy($phrase, $distance);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function expression($value)
    {
        $this->criteria = $this->criteria->expression($value);

        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function greaterThanEqual($value)
    {
        $this->criteria = $this->criteria->greaterThanEqual($value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function greaterThan($value)
    {
        $this->criteria = $this->criteria->greaterThan($value);
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function lessThanEqual($value)
    {
        $this->criteria = $this->criteria->lessThanEqual($value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function lessThan($value)
    {
        $this->criteria = $this->criteria->lessThan($value);
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function boost($value)
    {
        $this->criteria = $this->criteria->boost($value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getQuery()
    {
        $query = new SolrQuery();
        $query->setSolr($this->solr);
        $query->setRows(1000000);
        $query->setCustomQuery($this->criteria->getQuery());
        $query->setIndex($this->metaInformation->getIndex());
        $query->setEntity($this->metaInformation->getEntity());
        $query->setMetaInformation($this->metaInformation);

        return $query;
    }

    /**
     * @return Criteria
     */
    public function getCriteria()
    {
        return $this->criteria;
    }
}
