<?php

namespace Ae\WhiteLabelBundle\Service;

use Ae\WhiteLabelBundle\Exception\OperatorNotValidException;
use Ae\WhiteLabelBundle\Exception\WebsiteNotValidException;
use Ae\WhiteLabelBundle\Model\Website;
use Symfony\Component\Security\Core\User\UserInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class WhiteLabel
{
    const METHOD_BY_HOST = 'byHost';

    private $validOperators = ['not', 'and', 'or'];

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var PropertyAccessor
     */
    private $accessor;

    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * ContainerInterface constructor.
     *
     * @param $container
     * @param $logger
     */
    public function __construct($container, LoggerInterface $logger)
    {
        $this->accessor = PropertyAccess::createPropertyAccessor();
        $this->container = $container;
        $this->logger = $logger;
    }

    /**
     * @param $names
     * @param null $operator
     *
     * @throws OperatorNotValidException
     *
     * @return bool
     */
    public function enabled($names = [], $operator = null)
    {
        $websites = array_keys($this->container->getParameter('ae_white_label.websites'));
        if (!is_null($operator) && !in_array($operator, $this->validOperators)) {
            throw new OperatorNotValidException($operator);
        }

        if (empty($names)) {
            $default = $this->container->getParameter('ae_white_label.default');
            $method = $this->container->getParameter(sprintf('ae_white_label.website.%s.method', $default));

            return $this->$method($default);
        }

        foreach ($names as $name) {
            $name = defined($name) ? constant($name) : $name;

            if (!in_array($name, $websites)) {
                return false;
            }
            $method = $this->container->getParameter(sprintf('ae_white_label.website.%s.method', $name));
            if (is_null($operator)) {
                return $this->$method($name);
            } elseif ($operator === 'not' && !$this->$method($name)) {
                return true;
            } elseif ($operator === 'and' && !$this->$method($name)) {
                return false;
            } elseif ($operator === 'or' && $this->$method($name)) {
                return true;
            }
        }

        return $operator === 'and';
    }

    /**
     * @throws OperatorNotValidException
     *
     * @return Website
     */
    public function getWebsite()
    {
        $websites = array_keys($this->container->getParameter('ae_white_label.websites'));

        $defaultWebsite = null;
        foreach ($websites as $website) {
            $websiteModel = new Website($website, $this->container->getParameter(sprintf('ae_white_label.website.%s.model', $website)));
            if ($this->enabled([$website])) {
                return $websiteModel;
            } elseif ($website == $this->container->getParameter('ae_white_label.default')) {
                $defaultWebsite = $websiteModel;
            }
        }

        return $defaultWebsite;
    }

    /**
     * @param string $website
     *
     * @throws WebsiteNotValidException
     *
     * @return Website
     */
    public function getWebsiteByName($website)
    {
        $websites = array_keys($this->container->getParameter('ae_white_label.websites'));
        $website = strtolower($website);

        if (!in_array($website, $websites)) {
            throw new WebsiteNotValidException($website);
        }

        return new Website($website, $this->container->getParameter(sprintf('ae_white_label.website.%s.model', $website)));
    }

    /**
     * @param string $origin
     * @param string $originalFullUrl
     *
     * @return string
     */
    public function getWhitelabelUrlByOrigin($origin, $originalFullUrl = '')
    {
        try {
            $host = $this->getWebsiteByName($origin)->getHost();
        } catch (WebsiteNotValidException $e) {
            $this->logger->error($e->getMessage(), ['origin' => $origin]);
            $host = '';
        }

        if (empty($host)) {
            $defaultWebsite = $this->container->getParameter('ae_white_label.default');
            $host = $this->getWebsiteByName($defaultWebsite)->getHost();
        }

        $scheme = parse_url($originalFullUrl, PHP_URL_SCHEME);
        if (is_null($scheme)) {
            $scheme = $this->container->hasParameter('router.request_context.scheme') ?
                $this->container->getParameter('router.request_context.scheme') : 'http';
        }

        return sprintf('%s://%s%s', $scheme, $host, parse_url($originalFullUrl, PHP_URL_PATH));
    }

    public function byHost($name)
    {
        $host = $this->container->getParameter(sprintf('ae_white_label.website.%s.host', $name));
        $currentHost = $this->container->get('router')->getContext()->getHost();

        return strtolower($host) == strtolower($currentHost);
    }

    public function byUserParam($name)
    {
        try {
            $paramKey = $this->container->getParameter(sprintf('ae_white_label.website.%s.user_param.key', $name));
            $paramValue = $this->container->getParameter(sprintf('ae_white_label.website.%s.user_param.value', $name));

            $buildParam = explode('.', $paramKey);
            $value = null;

            /** @var TokenStorage $tokenStorage */
            $tokenStorage = $this->container->get('security.token_storage');

            $token = $tokenStorage->getToken();
            if (!$token instanceof TokenInterface) {
                return false;
            }

            $user = $token->getUser();
            if (!$user instanceof UserInterface) {
                return false;
            }
            $value = $this->accessor->getValue($user, array_shift($buildParam));
            if (count($buildParam)) {
                foreach ($buildParam as $param) {
                    $value = $this->accessor->getValue($value, $param);
                }
            }
        } catch (\Exception $e) {
            return false;
        }

        return $value == $paramValue;
    }
}
