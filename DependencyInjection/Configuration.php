<?php

namespace Ae\WhiteLabelBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ae_white_label');

        $rootNode
            ->children()
                ->scalarNode('default_website')->end()
                ->append($this->getWhiteLabelsNode())
            ->end()
        ;

        return $treeBuilder;
    }

    /**
     * Return the websites node.
     *
     * @return ArrayNodeDefinition
     */
    private function getWhiteLabelsNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('websites');

        $node
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('name')
            ->prototype('array')
                ->children()
                    ->scalarNode('label')->defaultNull()->end()
                    ->scalarNode('host')->defaultNull()->end()
                    ->arrayNode('user_param')
                        ->children()
                            ->scalarNode('key')->defaultNull()->end()
                            ->scalarNode('value')->defaultNull()->end()
                        ->end()
                    ->end()
                    ->scalarNode('method')->defaultNull()->end()
                    ->arrayNode('custom_params')
                        ->prototype('variable')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }
}
