<?php


namespace FS\SolrBundle\Tests;

class DocumentStub implements \Solarium\QueryType\Update\Query\Document\DocumentInterface
{
    public $id = 1;
    public $document_name_s = 'stub_document';

    /**
     * Constructor
     *
     * @param array $fields
     * @param array $boosts
     * @param array $modifiers
     */
    public function __construct(array $fields = array(), array $boosts = array(), array $modifiers = array())
    {

    }

    /**
     * @param string $fieldName
     *
     * @return mixed
     */
    public function getField($fieldName)
    {
        $fields = array('id' => $this->id, 'document_name' => $this->document_name_s);

        return $fields[$fieldName];
    }
}