<?php

namespace Ae\WhiteLabelBundle\Exception;

class WebsiteNotValidException extends \Exception
{
    public function __construct($website, $code = 0, \Exception $previous = null)
    {
        parent::__construct('website '.$website.' not valid', $code, $previous);
    }
}
