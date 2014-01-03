<?php
namespace FS\SolrBundle\Query;

use FS\SolrBundle\Solr;

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
    private $useWildcards = true;

    /**
     *
     * @var Solr
     */
    private $solr = null;

    /**
     * @param \FS\SolrBundle\Solr $solr
     */
    public function setSolr($solr)
    {
        $this->solr = $solr;
    }

    /**
     * @return \FS\SolrBundle\Solr
     */
    public function getSolr()
    {
        return $this->solr;
    }

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
     * @param bool $strict
     */
    public function setUseWildcard($strict)
    {
        $this->useWildcards = $strict;
    }

    /**
     * @return array
     */
    public function getSearchTerms()
    {
        return $this->searchTerms;
    }

    /**
     * @param array $value
     */
    public function queryAllFields($value)
    {
        $this->setUseAndOperator(false);

        foreach ($this->mappedFields as $documentField => $entityField) {
            $this->searchTerms[$documentField] = $value;
        }
    }

    /**
     *
     * @param string $field
     * @param string $value
     * @return SolrQuery
     */
    public function addSearchTerm($field, $value)
    {
        $documentFieldsAsValues = array_flip($this->mappedFields);

        if (array_key_exists($field, $documentFieldsAsValues)) {
            $documentFieldName = $documentFieldsAsValues[$field];

            $this->searchTerms[$documentFieldName] = $value;
        }

        return $this;
    }

    /**
     * @param string $field
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
        $term = '';
        if (count($this->searchTerms) == 0) {
            return $term;
        }

        $logicOperator = 'AND';
        if (!$this->useAndOperator) {
            $logicOperator = 'OR';
        }

        $termCount = 1;
        foreach ($this->searchTerms as $fieldName => $fieldValue) {

            if ($this->useWildcards) {
                $term .= $fieldName . ':*' . $fieldValue . '*';
            } else {
                $term .= $fieldName . ':' . $fieldValue;
            }

            if ($termCount < count($this->searchTerms)) {
                $term .= ' ' . $logicOperator . ' ';
            }

            $termCount++;
        }

        $this->setQuery($term);

        return $term;
    }
}
