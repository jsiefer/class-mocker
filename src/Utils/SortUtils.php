<?php
/**
 * This file is part of ClassMocker.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  JSiefer\ClassMocker
 */
namespace JSiefer\ClassMocker\Utils;

use JSiefer\ClassMocker\Reflection\ClassReflection;


/**
 * Class StringUtils
 */
class SortUtils
{
    /**
     * Sort class reflections by their sort property
     *
     * @param ClassReflection[] $classes
     * @return ClassReflection[]
     */
    public static function sortReflections($classes)
    {
        usort($classes, [self::class, '__classSort']);

        return $classes;
    }

    /**
     * Callback for trait sort
     *
     * @param ClassReflection $a
     * @param ClassReflection $b
     *
     * @return int
     */
    protected static function __classSort(ClassReflection $a, ClassReflection $b)
    {
        if ($a->getSort() > $b->getSort()) {
            return -1;
        }
        if ($a->getSort() < $b->getSort()) {
            return 1;
        }
        return 0;
    }
}
