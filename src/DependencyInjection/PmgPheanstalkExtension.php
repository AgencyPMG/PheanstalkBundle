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

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;
use Pheanstalk\Pheanstalk;
use Pheanstalk\PheanstalkInterface;

/**
 * DI configuration for the bundle.
 *
 * @since    1.0
 */
final class PmgPheanstalkExtension extends ConfigurableExtension
{
    /**
     * {@inheritdoc}
     */
    protected function loadInternal(array $config, ContainerBuilder $container)
    {
        $connections = [];
        foreach ($config['connections'] as $name => $connConfig) {
            $connections[$name] = $this->loadConnection($container, $name, $connConfig);
        }

        $default = $config['default_connection'];
        if (!isset($connections[$default])) {
            throw new \LogicException(sprintf(
                'No Pheanstalk connection named "%s" in the configuration, cannot set the default connection',
                $config['default_connection']
            ));
        }

        $container->setAlias('pmg_pheanstalk', $connections[$default]);
        $container->setParameter('pmg_pheanstalk.params.default_conn', $default);
        $container->setParameter('pmg_pheanstalk.params.connections', $connections);

        $container->setDefinition('pmg_pheanstalk.stats_service', new Definition(
            'PMG\PheanstalkBundle\Service\PheanstalkStatsService',
            [new Reference('service_container')]
        ));

        $container->setDefinition('pmg_pheanstalk.queue_controller', new Definition(
            'PMG\PheanstalkBundle\Controller\QueueController',
            [new Reference('pmg_pheanstalk.stats_service')]
        ));
    }

    private function loadConnection(ContainerBuilder $container, $name, array $config)
    {
        $name = self::serviceName($name);
        $container->setDefinition($name, new Definition(Pheanstalk::class, [
            $config['host'],
            empty($config['port']) ? PheanstalkInterface::DEFAULT_PORT : $config['port'],
            isset($config['timeout']) ? $config['timeout'] : null,
            self::asBool(isset($config['persist']) ? $config['persist'] : false)
        ]));

        return $name;
    }

    private static function asBool($bool)
    {
        return filter_var($bool, FILTER_VALIDATE_BOOLEAN);
    }

    private static function serviceName($name)
    {
        return sprintf('pmg_pheanstalk.%s', $name);
    }

    /**
     * Loads all internal service definition files into the container
     */
    private function loadFiles(array $config, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );

        foreach ($config as $key => $val) {
            $container->setParameter("app.{$key}", $val);
        }

        $files = [
            'services.xml'
        ];

        foreach ($files as $fn) {
            $loader->load($fn);
        }
    }
}
