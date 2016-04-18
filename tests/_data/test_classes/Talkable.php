<?php
/**
 * Created by PhpStorm.
 * User: jsiefer
 * Date: 25/03/16
 * Time: 16:23
 */

namespace JSiefer\ClassMocker\TestClasses;


interface Talkable
{
    /**
     * @param $what
     * @param int $volume
     *
     * @param Human $target
     *
     * @return string
     */
    public function talk($what, $volume = 100, Human &$target = null);
}
