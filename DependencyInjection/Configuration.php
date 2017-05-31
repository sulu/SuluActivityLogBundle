<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\ActivityLogBundle\DependencyInjection;

use Sulu\Bundle\ElasticsearchActivityLogBundle\Storage\ElasticsearchActivityStorage;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Initializes configuration tree for activity-log-bundle.
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $storages = ['array', 'custom'];

        if (class_exists(ElasticsearchActivityStorage::class)) {
            $storages[] = 'elastic';
        }

        $treeBuilder = new TreeBuilder();
        $treeBuilder->root('sulu_activity_log')
            ->children()
                ->enumNode('storage')
                    ->values($storages)->end()
                ->append($this->storagesConfig());

        return $treeBuilder;
    }

    private function storagesConfig()
    {
        $treeBuilder = new TreeBuilder();
        $children = $treeBuilder->root('storages')
            ->children()
                ->arrayNode('array')
                ->end()
                ->arrayNode('custom')
                    ->children()
                        ->scalarNode('id')->end()
                    ->end()
                ->end();

        if (class_exists(ElasticsearchActivityStorage::class)) {
            $children->arrayNode('elastic')
                ->children()
                    ->scalarNode('ongr_manager')->end()
                ->end();
        }

        return $children->end();
    }
}
