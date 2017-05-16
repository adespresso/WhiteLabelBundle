<?php

namespace Ae\WhiteLabelBundle\Model;

class Website
{
    private $name;

    private $label;

    private $host;

    private $method;

    private $userParam;

    private $customParams;

    public function __construct($name, $website)
    {
        $this->name = $name;
        $this->method = $website['method'];
        $this->label = $website['label'];
        $this->host = key_exists('host', $website) ? $website['host'] : null;
        $this->userParam = key_exists('user_param', $website) ? $website['user_param'] : null;
        $this->customParams = $website['custom_params'];
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param mixed $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @return mixed
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param mixed $host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param mixed $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * @return mixed
     */
    public function getUserParam()
    {
        return $this->userParam;
    }

    /**
     * @param mixed $userParam
     */
    public function setUserParam($userParam)
    {
        $this->userParam = $userParam;
    }

    /**
     * @return mixed
     */
    public function getCustomParams()
    {
        return $this->customParams;
    }

    /**
     * @param mixed $customParams
     */
    public function setCustomParams($customParams)
    {
        $this->customParams = $customParams;
    }

    /**
     * @param $name
     *
     * @return mixed|null
     */
    public function getCustomParamByName($name)
    {
        if (key_exists($name, $this->customParams)) {
            return $this->customParams[$name];
        }
    }
}
