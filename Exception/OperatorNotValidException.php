<?php

namespace Ae\WhiteLabelBundle\Exception;

class OperatorNotValidException extends \Exception
{
    public function __construct($operator, $code = 0, \Exception $previous = null)
    {
        parent::__construct('operator '.$operator.' not valid', $code, $previous);
    }
}
