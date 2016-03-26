<?php

namespace JSiefer\ClassMocker\TestClasses;

/**
 * Class DummyTrait
 *
 * A simple trait fot testing purpose
 */
trait DummyTrait
{
    /**
     * @param $what
     * @param int $volume
     *
     * @param Human $target
     *
     * @return string
     */
    public function talk($what, $volume = 100, Human &$target = null)
    {
        return 'DummyTrait:talk';
    }

    /**
     * Simple protected method
     *
     * @param $a
     * @return mixed
     */
    protected function protectedMethod($a)
    {
        return $a + 10;
    }

    /**
     * Simple protected method
     *
     * @param $a
     * @return mixed
     */
    private function privateMethod($a)
    {
        return $a + 10;
    }

    /**
     * Simple protected method
     *
     * @param $a
     * @return mixed
     */
    public function publicMethod($a)
    {
        return $a + 10;
    }
}
