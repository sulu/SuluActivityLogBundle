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

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This is the class that loads and manages activity log bundle configuration.
 */
class SuluActivityLogExtension extends Extension
{
    const STORAGE_ELASTIC = 'elastic';
    const STORAGE_ARRAY = 'array';
    const STORAGE_CUSTOM = 'custom';

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        switch ($config['storage']) {
            case self::STORAGE_ELASTIC:
                $id = $this->initElasticSearch($config['storages']['elastic'], $container);
                break;

            case self::STORAGE_ARRAY:
                $id = $this->initArray($config['storages']['array'], $container);
                break;

            case self::STORAGE_CUSTOM:
                $id = $this->initCustom($config['storages']['custom']);
                break;
        }

        $container->setAlias('sulu_activity_log.activity_log_storage', $id);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');
    }

    /**
     * @param array $config
     * @param ContainerBuilder $container
     *
     * @return string
     */
    private function initElasticSearch(array $config, ContainerBuilder $container)
    {
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('storages/elastic.xml');

        if (!$config['ongr_manager']) {
            $error = new InvalidConfigurationException();
            $error->setPath('sulu_activity_log.storages.elastic.ongr_manager');

            throw $error;
        }

        $container->getDefinition('sulu_activity_log.storage.elastic')->replaceArgument(
            0,
            new Reference('es.manager.' . $config['ongr_manager'])
        );

        return 'sulu_activity_log.storage.elastic';
    }

    private function initArray($config, $container)
    {
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('storages/array.xml');

        return 'sulu_activity_log.storage.array';
    }

    private function initCustom($config)
    {
        if (!$config['id']) {
            $error = new InvalidConfigurationException();
            $error->setPath('sulu_activity_log.storages.custom.id');

            throw $error;
        }

        return 'sulu_activity_log.storage.custom';
    }
}
