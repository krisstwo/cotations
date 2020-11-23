<?php
/**
 * Coffee & Brackets software studio
 * @author Mohamed KRISTOU <krisstwo@gmail.com>.
 */

namespace App\DependencyInjection;


use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('easy-price');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('configuration_keys')
                ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->useAttributeAsKey('name')
                            ->arrayPrototype()
                                ->children()
                                    ->scalarNode('type')->isRequired()->end()
                                    ->scalarNode('label')->isRequired()->end()
                                    ->scalarNode('description')->end()
                                    ->arrayNode('tags')->scalarPrototype()->end()
                            ->end()
                    ->end()
            ->end();

        return $treeBuilder;
    }
}