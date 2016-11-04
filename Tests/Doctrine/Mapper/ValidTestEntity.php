<?php
namespace FS\SolrBundle\Tests\Doctrine\Mapper;

use FS\SolrBundle\Doctrine\Annotation as Solr;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @Solr\Document(boost="1")
 */
class ValidTestEntity
{

    /**
     * @Solr\Id
     */
    private $id;

    /**
     * @Solr\Field(type="text")
     *
     * @var text
     */
    private $text;

    /**
     * @Solr\Field()
     *
     * @var text
     */
    private $title;

    /**
     * @Solr\Field(type="date", getter="format('d.m.Y')")
     *
     * @var date
     */
    private $created_at;

    /**
     * @Solr\Field(type="my_costom_fieldtype")
     *
     * @var string
     */
    private $costomField;

    /**
     * @var ValidTestEntity[]
     */
    private $posts;

    /**
     * @var string
     */
    private $publishDate;

    /**
     * @var string
     */
    private $privateField;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return the $text
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @return the $title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param \FS\BlogBundle\Tests\Solr\Doctrine\Mapper\text $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * @param \FS\BlogBundle\Tests\Solr\Doctrine\Mapper\text $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @param string $costomField
     */
    public function setCostomField($costomField)
    {
        $this->costomField = $costomField;
    }

    /**
     * @return string
     */
    public function getCostomField()
    {
        return $this->costomField;
    }

    /**
     * @return \FS\SolrBundle\Tests\Doctrine\Mapper\date
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * @param \FS\SolrBundle\Tests\Doctrine\Mapper\date $created_at
     */
    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
    }

    /**
     * @return ValidTestEntity[]
     */
    public function getPosts()
    {
        return $this->posts;
    }

    /**
     * @param ValidTestEntity[] $posts
     */
    public function setPosts($posts)
    {
        $this->posts = $posts;
    }

    public function setField($field)
    {
        $this->privateField = $field;
    }

    public function getField()
    {
        return $this->privateField;
    }

    /**
     * @return string
     */
    public function getPublishDate()
    {
        return $this->publishDate;
    }

    /**
     * @param string $publishDate
     */
    public function setPublishDate($publishDate)
    {
        $this->publishDate = $publishDate;
    }
}

