<?php

namespace Ae\WhiteLabelBundle\DependencyInjection;

use Ae\WhiteLabelBundle\Exception\DefaultWebsiteNotExistsException;
use Ae\WhiteLabelBundle\Exception\PriorityValueAlreadyUsedException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class AeWhiteLabelExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);
        $this->loadConfig($config, $container);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
    }

    public function loadConfig($config, ContainerBuilder $container)
    {
        $websites = [];
        $priorities = [];

        foreach ($config['websites'] as $name => $website) {
            $this->configureWhiteLabel($name, $website, $container);
            $priority = $website['priority'];
            $this->checkPriority($priorities, $priority, $name);

            $container->setParameter(sprintf('ae_white_label.website.%s.model', $name), $website);
            $priorities[$priority] = $name;
            $websites[$name] = sprintf('ae_white_label.website.%s', $name);
        }

        $websites = $this->sortWebsitesByPriorities($websites, $priorities);

        $default = $config['default_website'];
        if (isset($websites[$default])) {
            $container->setParameter('ae_white_label.default', $default);
        } else {
            throw new DefaultWebsiteNotExistsException(sprintf('Default is %s, but this website does not exist', $default));
        }
        $container->setParameter('ae_white_label.websites', $websites);
    }

    protected function configureWhiteLabel($name, array $website, ContainerBuilder $container)
    {
        $container->setParameter(sprintf('ae_white_label.website.%s.method', $name), $website['method']);
        $container->setParameter(sprintf('ae_white_label.website.%s.label', $name), $website['label']);
        $container->setParameter(sprintf('ae_white_label.website.%s.priority', $name), $website['priority']);
        if (key_exists('host', $website)) {
            $container->setParameter(sprintf('ae_white_label.website.%s.host', $name), $website['host']);
        }
        if (key_exists('user_param', $website)) {
            $container->setParameter(sprintf('ae_white_label.website.%s.user_param.key', $name), $website['user_param']['key']);
            $container->setParameter(sprintf('ae_white_label.website.%s.user_param.value', $name), $website['user_param']['value']);
        }
        // set custom_params as parameters if exists
        if (key_exists('custom_params', $website)) {
            foreach ($website['custom_params'] as $param => $value) {
                $container->setParameter(sprintf('ae_white_label.website.%s.%s', $name, $param), $value);
            }
        }
    }

    /**
     * @param array $priorities
     * @param string $priority
     *
     * @param string $name
     *
     * @throws PriorityValueAlreadyUsedException
     */
    private function checkPriority(array $priorities, $priority, $name)
    {
        if (key_exists($priority, $priorities)) {
            throw new PriorityValueAlreadyUsedException(
                    $priority,
                    $name,
                    $priorities[$priority]
            );
        }
    }

    /**
     * @param array $websites
     * @param array $priorities
     *
     * @return array
     */
    private function sortWebsitesByPriorities(array $websites, array $priorities)
    {
        $sortedWebsite = [];
        ksort($priorities);

        foreach ($priorities as $id) {
            $sortedWebsite[$id] = $websites[$id];
        }

        return $sortedWebsite;
    }
}
