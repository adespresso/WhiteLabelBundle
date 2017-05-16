<?php

namespace Ae\WhiteLabelBundle\Twig\Extension;

use Ae\WhiteLabelBundle\Model\Website;
use Ae\WhiteLabelBundle\Service\WhiteLabel;
use Twig_Extension;

class WebsiteLabelExtension extends Twig_Extension
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

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('website', [$this, 'getWebsite']),
            new \Twig_SimpleFunction('impersonateUrl', [$this, 'getWhitelabelUrlByOrigin']),
        ];
    }

    public function getName()
    {
        return 'website';
    }

    /**
     * @return Website
     */
    public function getWebsite()
    {
        return $this->service->getWebsite();
    }

    /**
     * @param string $origin
     * @param string $originalFullUrl
     *
     * @return string
     */
    public function getWhitelabelUrlByOrigin($origin, $originalFullUrl = '')
    {
        return $this->service->getWhitelabelUrlByOrigin($origin, $originalFullUrl);
    }
}
