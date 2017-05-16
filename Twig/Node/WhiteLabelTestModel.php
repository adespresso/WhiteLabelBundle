<?php

namespace Ae\WhiteLabelBundle\Twig\Node;

class WhiteLabelTestModel
{
    public $names;
    public $operator;
    public $body;

    public function __construct($names, $operator, $body)
    {
        $this->names = $names;
        $this->body = $body;
        $this->operator = $operator;
    }
}
