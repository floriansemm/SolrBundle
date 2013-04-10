<?php
namespace FS\SolrBundle\Tests;

class SolrClientFake
{
    private $commit = false;
    private $response = array();

    public function addDocument($doc)
    {
    }

    public function deleteByQuery($query)
    {
    }

    public function commit()
    {
        $this->commit = true;
    }

    public function isCommited()
    {
        return $this->commit;
    }

    public function query()
    {
        return $this->response;
    }

    public function setResponse(SolrResponseFake $response)
    {
        $this->response = $response;
    }

    public function getOptions()
    {
        return array();
    }
}
