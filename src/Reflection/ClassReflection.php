<?php
/**
 * This file is part of ClassMocker.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  JSiefer\ClassMocker
 */
namespace JSiefer\ClassMocker\Reflection;

use Zend\Code\Reflection\ClassReflection as ZendClassReflection;
use Zend\Code\Reflection\DocBlock\Tag\GenericTag;


/**
 * Class TraitReflection
 */
class ClassReflection extends ZendClassReflection
{
    /**
     *
     * @var string
     */
    protected $_pattern;

    /**
     * @var float
     */
    protected $_sort;

    /**
     * TraitReflection constructor.
     *
     * @param string $argument
     * @param null $pattern
     * @param float $sort
     */
    public function __construct($argument, $pattern = null, $sort = null)
    {
        parent::__construct($argument);
        $this->setPattern($pattern);
        $this->setSort($sort);
    }

    /**
     * Check if trait is made _pattern given className
     *
     * @param string $className
     *
     * @return bool
     */
    public function matchClassName($className)
    {
        return fnmatch($this->getPattern(), $className, FNM_NOESCAPE);
    }

    /**
     * Extract a @tag value from the doc comment
     *
     * @param string $tag
     * @param string $default
     *
     * @return string
     */
    protected function extractTag($tag, $default = '')
    {
        $doc = $this->getDocBlock();
        if (!$doc) {
            return $default;
        }

        $tag = $doc->getTag($tag);
        if ($tag instanceof GenericTag) {
            return $tag->getContent();
        }

        return $default;
    }

    /**
     * Retrieve defined _pattern tag
     *
     * @return string
     */
    public function getPattern()
    {
        if ($this->_pattern === null) {
            $this->_pattern = $this->extractTag('pattern');
        }

        return $this->_pattern;
    }

    /**
     * @param string $pattern
     *
     * @return $this
     */
    public function setPattern($pattern)
    {
        $this->_pattern = $pattern;

        return $this;
    }

    /**
     * Retrieve defined sort tag
     *
     * @return float
     */
    public function getSort()
    {
        if ($this->_sort === null) {
            $this->_sort = (float)$this->extractTag('sort', 0);
        }

        return $this->_sort;
    }

    /**
     * @param float $sort
     *
     * @return $this
     */
    public function setSort($sort)
    {
        $this->_sort = $sort;

        return $this;
    }
}
