<?php


namespace FS\SolrBundle\Tests\Integration\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use FS\SolrBundle\Doctrine\Annotation as Solr;

/**
 * Post
 *
 * @Solr\Nested()
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Category {
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Solr\Id
     */
    private $id;

    /**
     * @var string
     *
     * @Solr\Field(type="string")
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @var string
     *
     * @Solr\Field(type="string")
     *
     * @ORM\Column(name="info", type="string", length=255, nullable=true)
     */
    private $info;

    /**
     * @var Post[]
     *
     * Solr\Field(type="strings", getter="getTitle")
     * @ORM\OneToMany(targetEntity="Acme\DemoBundle\Entity\Post", mappedBy="category")
     */
    private $posts;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
    }

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
     * @return Post[]
     */
    public function getPosts()
    {
        return $this->posts;
    }

    /**
     * @param Post[] $posts
     */
    public function setPosts($posts)
    {
        $this->posts = $posts;
    }

    public function addPost($post)
    {
        $this->posts->add($post);
    }

    /**
     * @param string $info
     */
    public function setInfo($info)
    {
        $this->info = $info;
    }

}