<?php
/*
 * This file is part of pmg/pheanstalk-bundle
 *
 * Copyright (c) PMG <https://www.pmg.com>
 *
 * For full copyright information see the LICENSE file distributed
 * with this source code.
 *
 * @license     http://opensource.org/licenses/Apache-2.0 Apache-2.0
 */

namespace PMG\PheanstalkBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Pheanstalk\PheanstalkInterface;

/**
 * The configuration definition. This just defines the host port and timeout
 * for each connection.
 *
 * @since   1.0
 */
final class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $tree = new TreeBuilder();
        $root = $tree->root('pmg_pheanstalk');

        $root
            ->beforeNormalization()
                ->ifTrue(function ($config) {
                    return is_array($config) && !array_key_exists('connections', $config);
                })
                ->then(function ($config) {
                    $default = isset($config['default_connection']) ? $config['default_connection'] : 'default';
                    unset($config['default_connection']);

                    return [
                        'default_connection'    => $default,
                        'connections'           => ['default' => $config],
                    ];
                })
            ->end()
        ;

        $root
            ->children()
            ->append($this->createConnectionsNode())
            ->scalarNode('default_connection')
                ->cannotBeEmpty()
                ->defaultValue('default')
                ->info("The default connection's name")
            ->end()
        ;

        return $tree;
    }

    private function createConnectionsNode()
    {
        $tree = new TreeBuilder();
        $root = $tree->root('connections');

        $root
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('name')
            ->prototype('array')
            ->children()
                ->scalarNode('host')
                    ->cannotBeEmpty()
                    ->defaultValue('localhost')
                    ->info("The connection's host.")
                ->end()
                ->integerNode('port')
                    ->info("The connection's port.")
                    ->defaultValue(PheanstalkInterface::DEFAULT_PORT)
                ->end()
                ->integerNode('timeout')
                    ->info("The connection's timeout.")
                    ->defaultValue(null)
                ->end()
                ->booleanNode('persist')
                    ->info("Whether or not to keep the connection's socket around between requests. See http://php.net/manual/en/function.pfsockopen.php")
                    ->defaultValue(false)
                ->end()
            ->end()
        ;

        return $root;
    }
}
