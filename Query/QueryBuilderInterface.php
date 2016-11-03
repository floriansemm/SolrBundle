<?php

namespace FS\SolrBundle\Query;

use FS\SolrBundle\Query\Exception\UnknownFieldException;

interface QueryBuilderInterface
{
    /**
     * @param string $field
     *
     * @return QueryBuilderInterface
     *
     * @throws UnknownFieldException if $field does not exists
     */
    public function where($field);

    /**
     * @param string $field
     *
     * @return QueryBuilderInterface
     *
     * @throws UnknownFieldException if $field does not exists
     */
    public function andWhere($field);

    /**
     * @param string $field
     *
     * @return QueryBuilderInterface
     *
     * @throws UnknownFieldException if $field does not exists
     */
    public function orWhere($field);

    /**
     * @param mixed $lowerBound
     * @param mixed $upperBound
     * @param bool  $includeLowerBound
     * @param bool  $includeUpperBound
     *
     * @return QueryBuilderInterface
     */
    public function between($lowerBound, $upperBound, $includeLowerBound = true, $includeUpperBound = true);

    /**
     * @param mixed $value
     *
     * @return QueryBuilderInterface
     */
    public function is($value);

    /**
     * @param array $values
     *
     * @return QueryBuilderInterface
     */
    public function in(array $values);

    /**
     * @param float $latitude
     * @param float $longitude
     * @param float $distance
     *
     * @return QueryBuilderInterface
     */
    public function withinCircle($latitude, $longitude, $distance);

    /**
     * @param float $startLatitude
     * @param float $startLongitude
     * @param float $endLatitude
     * @param float $endLongitude
     *
     * @return QueryBuilderInterface
     */
    public function withinBox($startLatitude, $startLongitude, $endLatitude, $endLongitude);

    /**
     * @param float $latitude
     * @param float $longitude
     * @param int   $distance
     *
     * @return QueryBuilderInterface
     */
    public function nearCircle($latitude, $longitude, $distance);

    /**
     * @return QueryBuilderInterface
     */
    public function isNull();

    /**
     * @return QueryBuilderInterface
     */
    public function isNotNull();

    /**
     * @param string $value
     *
     * @return QueryBuilderInterface
     */
    public function contains($value);

    /**
     * @param string $prefix
     *
     * @return QueryBuilderInterface
     */
    public function startsWith($prefix);

    /**
     * @param string $postfix
     *
     * @return QueryBuilderInterface
     */
    public function endsWith($postfix);

    /**
     * @return QueryBuilderInterface
     */
    public function not();

    /**
     * @return QueryBuilderInterface
     */
    public function notOperator();

    /**
     * @param string $value
     * @param float  $levenshteinDistance
     *
     * @return QueryBuilderInterface
     */
    public function fuzzy($value, $levenshteinDistance = null);

    /**
     * @param string $phrase
     * @param int    $distance
     *
     * @return QueryBuilderInterface
     */
    public function sloppy($phrase, $distance);

    /**
     * @param string $value
     *
     * @return QueryBuilderInterface
     */
    public function expression($value);

    /**
     * @return SolrQuery
     */
    public function getQuery();

    /**
     * @param string $value
     *
     * @return QueryBuilderInterface
     */
    public function greaterThanEqual($value);

    /**
     * @param string $value
     *
     * @return QueryBuilderInterface
     */
    public function lessThanEqual($value);

    /**
     * @param float $value
     *
     * @return QueryBuilderInterface
     */
    public function boost($value);
}