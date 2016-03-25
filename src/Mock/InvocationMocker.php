<?php
/**
 * Created by PhpStorm.
 * User: jsiefer
 * Date: 25/03/16
 * Time: 10:51
 */

namespace JSiefer\ClassMocker\Mock;


use JSiefer\ClassMocker\next;

class InvocationMocker extends \PHPUnit_Framework_MockObject_InvocationMocker
{
    /**
     * @param \PHPUnit_Framework_MockObject_Invocation $invocation
     *
     * @return mixed
     * @throws \Exception
     */
    public function invoke(\PHPUnit_Framework_MockObject_Invocation $invocation)
    {
        $exception      = null;
        $hasReturnValue = false;

        $returnValue = next::caller();

        foreach ($this->matchers as $match) {
            try {
                if ($match->matches($invocation)) {
                    $value = $match->invoked($invocation);

                    if (!$hasReturnValue) {
                        $returnValue    = $value;
                        $hasReturnValue = true;
                    }
                }
            } catch (\Exception $e) {
                $exception = $e;
            }
        }

        if ($exception !== null) {
            throw $exception;
        }

        return $returnValue;
    }
}
