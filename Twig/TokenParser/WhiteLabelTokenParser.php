<?php

namespace Ae\WhiteLabelBundle\Twig\TokenParser;

use Ae\WhiteLabelBundle\Twig\Node\WhiteLabelNode;
use Ae\WhiteLabelBundle\Twig\Node\WhiteLabelTestModel;
use Twig_Error_Syntax;
use Twig_Token;
use Twig_TokenParser;
use Twig_TokenStream;

class WhiteLabelTokenParser extends Twig_TokenParser
{
    private function parseBlock(Twig_TokenStream $stream)
    {
        $names = [];
        $operator = null;

        $endSentence = false;
        while (!$endSentence) {
            if ($stream->test(Twig_Token::OPERATOR_TYPE)) {
                $operator = $stream->next()->getValue();
                $names[] = $stream->next()->getValue();
            } elseif ($stream->test(Twig_Token::STRING_TYPE)) {
                $names[] = $stream->next()->getValue();
            } elseif (!$stream->test(Twig_Token::BLOCK_END_TYPE)) {
                $stream->next();
            }

            $endSentence = $stream->test(Twig_Token::BLOCK_END_TYPE);
        }

        return [$names, $operator];
    }

    public function parse(Twig_Token $token)
    {
        $testModels = [];

        $lineno = $token->getLine();
        $stream = $this->parser->getStream();

        list($name, $operator) = $this->parseBlock($stream);

        $stream->expect(Twig_Token::BLOCK_END_TYPE);
        $body = $this->parser->subparse([$this, 'decideWhiteLabelFork']);
        $testModels[] = new WhiteLabelTestModel($name, $operator, $body);
        $else = null;

        $end = false;
        while (!$end) {
            $value = $stream->next()->getValue();
            switch ($value) {
                case 'else':
                    $stream->expect(Twig_Token::BLOCK_END_TYPE);
                    $else = $this->parser->subparse([$this, 'decideWhiteLabelEnd']);
                    break;

                case 'elseif':
                    list($name, $operator) = $this->parseBlock($stream);
                    $stream->expect(Twig_Token::BLOCK_END_TYPE);
                    $body = $this->parser->subparse([$this, 'decideWhiteLabelFork']);

                    $testModels[] = new WhiteLabelTestModel($name, $operator, $body);
                    break;

                case 'endwhitelabel':
                    $end = true;
                    break;

                default:
                    throw new Twig_Error_Syntax(sprintf('Unexpected end of template. Twig was looking for the following tags "else", "elseif", or "endif" to close the "if" block started at line %d).', $lineno), $stream->getCurrent()->getLine(), $stream->getFilename());
            }
        }

        $stream->expect(Twig_Token::BLOCK_END_TYPE);

        return new WhiteLabelNode(
            $testModels,
            $else,
            $lineno,
            $this->getTag()
        );
    }

    public function decideWhiteLabelFork(Twig_Token $token)
    {
        return $token->test(['elseif', 'else', 'endwhitelabel']);
    }

    public function decideWhiteLabelEnd(Twig_Token $token)
    {
        return $token->test(['endwhitelabel']);
    }

    /**
     * Gets the tag name associated with this token parser.
     *
     * @return string The tag name
     */
    public function getTag()
    {
        return 'whitelabel';
    }
}
