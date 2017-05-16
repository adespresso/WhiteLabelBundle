<?php

namespace Ae\WhiteLabelBundle\Twig\Node;

use Twig_Node;
use Twig_Node_Expression_Array;
use Twig_Node_Expression_Constant;
use Twig_Node_Expression_ExtensionReference;
use Twig_Node_Expression_MethodCall;
use Twig_Node_If;

class WhiteLabelNode extends Twig_Node_If
{
    /**
     * WhiteLabelNode constructor.
     *
     * @param array $modeTests
     * @param int   $else
     * @param null  $lineno
     * @param null  $tag
     */
    public function __construct($modeTests, $else, $lineno, $tag = null)
    {
        $tests = [];

        foreach ($modeTests as $modelTest) {
            /* @var WhiteLabelTestModel $modelTest */
            $tests[] = $this->createExpression($modelTest->names, $modelTest->operator, $lineno);
            $tests[] = $modelTest->body;
        }

        parent::__construct(new Twig_Node($tests), $else, $lineno, $tag);
    }

    protected function createExpression($names, $operator, $lineno)
    {
        return new Twig_Node_Expression_MethodCall(
            new Twig_Node_Expression_ExtensionReference('whitelabel', $lineno),
            'enabled',
            new Twig_Node_Expression_Array(
                [
                    new Twig_Node_Expression_Constant('names', $lineno),
                    new Twig_Node_Expression_Constant($names, $lineno),
                    new Twig_Node_Expression_Constant('operator', $lineno),
                    new Twig_Node_Expression_Constant($operator, $lineno),
                ],
                $lineno
            ),
            $lineno
        );
    }
}
