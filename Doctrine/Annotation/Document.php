<?php
namespace FS\SolrBundle\Doctrine\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 */
class Document extends Annotation
{
    public $repository = '';
    public $boost = 0;
    public $index = 'default';
    public $indexHandler;

    /**
     * @throws \InvalidArgumentException if boost is not a number
     * @return number
     */
    public function getBoost()
    {
        if (!is_numeric($this->boost)) {
            throw new \InvalidArgumentException(sprintf('Invalid boost value %s', $this->boost));
        }
        
        $float = floatval($this->boost);
        return $float ?: null;
    }

    /**
     * @return string
     */
    public function getIndex()
    {
        return $this->index;
    }
}
