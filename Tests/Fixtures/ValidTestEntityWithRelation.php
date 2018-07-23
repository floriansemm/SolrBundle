<?php

namespace FS\SolrBundle\Tests\Fixtures;

use FS\SolrBundle\Doctrine\Annotation as Solr;

/**
 * @Solr\Document(boost="1")
 */
class ValidTestEntityWithRelation
{

    /**
     * @Solr\Id
     */
    private $id;

    /**
     * @Solr\Field(type="text")
     *
     * @var string
     */
    private $text;

    /**
     * @Solr\Field()
     *
     * @var string
     */
    private $title;

    /**
     * @Solr\Field(type="date")
     *
     * @var \DateTime
     */
    private $created_at;

    /**
     * @Solr\Field(type="my_custom_fieldtype")
     *
     * @var string
     */
    private $customField;

    /**
     * @var object
     *
     * @Solr\Field(type="strings", getter="getTitle")
     */
    private $relation;

    /**
     * @var object
     *
     * @Solr\Field(type="strings")
     */
    private $posts;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param string $customField
     */
    public function setCustomField($customField)
    {
        $this->customField = $customField;
    }

    /**
     * @return string
     */
    public function getCustomField()
    {
        return $this->customField;
    }

    /**
     * @return object
     */
    public function getRelation()
    {
        return $this->relation;
    }

    /**
     * @param object $relation
     */
    public function setRelation($relation)
    {
        $this->relation = $relation;
    }

    /**
     * @return object
     */
    public function getPosts()
    {
        return $this->posts;
    }

    /**
     * @param object $posts
     */
    public function setPosts($posts)
    {
        $this->posts = $posts;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param string $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * @param \DateTime $created_at
     */
    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
    }
}

