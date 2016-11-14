<?php

namespace FS\SolrBundle\Query;

use FS\SolrBundle\Query\Exception\UnknownFieldException;

class SolrQuery extends AbstractQuery
{

    /**
     * @var array
     */
    private $mappedFields = array();

    /**
     * @var array
     */
    private $searchTerms = array();

    /**
     * @var bool
     */
    private $useAndOperator = false;

    /**
     * @var bool
     */
    private $useWildcards = false;

    /**
     * @var string
     */
    private $customQuery;

    /**
     * @return array
     */
    public function getResult()
    {
        return $this->solr->query($this);
    }

    /**
     * @return array
     */
    public function getMappedFields()
    {
        return $this->mappedFields;
    }

    /**
     * @param array $mappedFields
     */
    public function setMappedFields($mappedFields)
    {
        $this->mappedFields = $mappedFields;
    }

    /**
     * @param bool $strict
     */
    public function setUseAndOperator($strict)
    {
        $this->useAndOperator = $strict;
    }

    /**
     * @param bool $boolean
     */
    public function setUseWildcard($boolean)
    {
        $this->useWildcards = $boolean;
    }

    /**
     * @return string
     */
    public function getCustomQuery()
    {
        return $this->customQuery;
    }

    /**
     * @param string $query
     */
    public function setCustomQuery($query)
    {
        $this->customQuery = $query;
    }

    /**
     * @return array
     */
    public function getSearchTerms()
    {
        return $this->searchTerms;
    }

    /**
     * @param string $value
     */
    public function queryAllFields($value)
    {
        $this->setUseAndOperator(false);

        foreach ($this->mappedFields as $documentField => $entityField) {
            if ($documentField == $this->getMetaInformation()->getIdentifierFieldName()) {
                continue;
            }

            $this->searchTerms[$documentField] = $value;
        }
    }

    /**
     *
     * @param string $field
     * @param string $value
     *
     * @return SolrQuery
     *
     * @throws UnknownFieldException if $field has not mapping / is unknown
     */
    public function addSearchTerm($field, $value)
    {
        $documentFieldsAsValues = array_flip($this->mappedFields);

        if (!array_key_exists($field, $documentFieldsAsValues)) {
            throw new UnknownFieldException(sprintf('Entity %s has no mapping for field %s', get_class($this->getEntity()), $field));
        }

        $documentFieldName = $documentFieldsAsValues[$field];
        $this->searchTerms[$documentFieldName] = $value;

        return $this;
    }

    /**
     * @param string $field
     *
     * @return SolrQuery
     */
    public function addField($field)
    {
        $entityFieldNames = array_flip($this->mappedFields);
        if (array_key_exists($field, $entityFieldNames)) {
            parent::addField($entityFieldNames[$field]);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        $keyField = $this->getMetaInformation()->getDocumentKey();

        $documentLimitation = $this->createFilterQuery('id')->setQuery('id:'.$keyField.'*');

        $this->addFilterQuery($documentLimitation);
        if ($this->customQuery) {
            parent::setQuery($this->customQuery);

            return $this->customQuery;
        }

        $term = '';
        // query all documents if no terms exists
        if (count($this->searchTerms) == 0) {
            $query = '*:*';
            parent::setQuery($query);

            return $query;
        }

        $logicOperator = 'AND';
        if (!$this->useAndOperator) {
            $logicOperator = 'OR';
        }

        $termCount = 1;
        foreach ($this->searchTerms as $fieldName => $fieldValue) {

            if ($fieldName == 'id') {
                $this->getFilterQuery('id')->setQuery('id:' . $fieldValue);

                $termCount++;

                continue;
            }

            $fieldValue = $this->querifyFieldValue($fieldValue);

            $term .= $fieldName . ':' . $fieldValue;

            if ($termCount < count($this->searchTerms)) {
                $term .= ' ' . $logicOperator . ' ';
            }

            $termCount++;
        }

        if (strlen($term) == 0) {
            $term = '*:*';
        }

        $this->setQuery($term);

        return $term;
    }

    /**
     * Transforms array to string representation and adds quotes
     *
     * @param string $fieldValue
     *
     * @return string
     */
    private function querifyFieldValue($fieldValue)
    {
        if (is_array($fieldValue) && count($fieldValue) > 1) {
            sort($fieldValue);

            $quoted = array_map(function($value) {
                return '"'. $value .'"';
            }, $fieldValue);

            $fieldValue = implode(' TO ', $quoted);
            $fieldValue = '['. $fieldValue . ']';

            return $fieldValue;
        }

        if (is_array($fieldValue) && count($fieldValue) === 1) {
            $fieldValue = array_pop($fieldValue);
        }

        if ($this->useWildcards) {
            $fieldValue = '*' . $fieldValue . '*';
        }

        $termParts = explode(' ', $fieldValue);
        if (count($termParts) > 1) {
            $fieldValue = '"'.$fieldValue.'"';
        }

        return $fieldValue;
    }
}
