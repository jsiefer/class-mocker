<?php
/**
 * Created by PhpStorm.
 * User: jsiefer
 * Date: 25/03/16
 * Time: 16:23
 */

namespace JSiefer\ClassMocker\TestClasses;


interface Readable
{
    /**
     * @param string $book
     * @param int $page
     *
     * @param array $lines
     *
     * @return string
     */
    public function read($book = '', $page = 0, array $lines = []);
}
