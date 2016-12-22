<?php
namespace FS\SolrBundle\Tests\Doctrine\Mapper;

use FS\SolrBundle\Doctrine\Annotation as Solr;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @Solr\Document(boost="1", index="*")
 */
class ValidTestEntityAllCores
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
     * @Solr\Field(type="date", getter="format('d.m.Y')")
     *
     * @var \DateTime
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

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string $text
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @return string $title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * @param string $title
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

    /**
     * @param string $field
     */
    public function setField($field)
    {
        $this->privateField = $field;
    }

    /**
     * @return string
     */
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

