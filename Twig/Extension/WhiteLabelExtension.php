<?php

namespace Ae\WhiteLabelBundle\Twig\Extension;

use Ae\WhiteLabelBundle\Service\WhiteLabel;
use Ae\WhiteLabelBundle\Twig\TokenParser\WhiteLabelTokenParser;
use Twig_Extension;

class WhiteLabelExtension extends Twig_Extension
{
    /**
     * @var WhiteLabel
     */
    protected $service;

    /**
     * @param WhiteLabel $service
     */
    public function __construct(WhiteLabel $service)
    {
        $this->service = $service;
    }

    /**
     * {@inheritdoc}
     */
    public function getTokenParsers()
    {
        return [
            new WhiteLabelTokenParser(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'whitelabel';
    }

    /**
     * @return bool
     */
    public function enabled($names = [], $operator = null)
    {
        return $this->service->enabled($names, $operator);
    }
}
