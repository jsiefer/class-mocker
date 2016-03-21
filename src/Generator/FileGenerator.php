<?php
/**
 * This file is part of ClassMocker.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  JSiefer\ClassMocker
 */
namespace JSiefer\ClassMocker\Generator;


use Zend\Code\Generator\DocBlock\Tag\AuthorTag;
use Zend\Code\Generator\DocBlock\Tag\GenericTag;
use Zend\Code\Generator\DocBlockGenerator;


/**
 * Class ClassGenerator
 */
class FileGenerator extends \Zend\Code\Generator\FileGenerator
{

    /**
     * FileGenerator constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $mockTag = new GenericTag();
        $mockTag->setName('mock');

        $author = new AuthorTag('ClassMocker');

        $docBlock = new DocBlockGenerator();
        $docBlock->setShortDescription("Auto generated file by ClassMocker, do not change");
        $docBlock->setTag($author);
        $docBlock->setTag($mockTag);

        $this->setDocBlock($docBlock);
    }

}
