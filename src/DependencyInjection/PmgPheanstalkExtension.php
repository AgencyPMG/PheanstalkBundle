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

use LogicException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use Pheanstalk\Pheanstalk;
use Pheanstalk\Contract\PheanstalkInterface;
use PMG\PheanstalkBundle\ConnectionManager;
use PMG\PheanstalkBundle\Service\PheanstalkStatsService;
use PMG\PheanstalkBundle\Service\StatsService;
use PMG\PheanstalkBundle\Service\QueueUtilities;
use PMG\PheanstalkBundle\Controller\QueueController;
use PMG\PheanstalkBundle\Command\PurgeQueueCommand;
use PMG\PheanstalkBundle\Command\StatsCommand;

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
            throw new LogicException(sprintf(
                'No Pheanstalk connection named "%s" in the configuration, cannot set the default connection',
                $config['default_connection']
            ));
        }

        $container->setAlias('pmg_pheanstalk', $connections[$default])
            ->setPublic(true);
        $container->setAlias(PheanstalkInterface::class, $connections[$default])
            ->setPublic(true);

        $container->register('pmg_pheanstalk.internal.connection_manager', ConnectionManager::class)
            ->addArgument(array_map(function (string $connectionService) : Reference {
                return new Reference($connectionService);
            }, $connections))
            ->addArgument($config['default_connection']);
        $container->setAlias(ConnectionManager::class, 'pmg_pheanstalk.internal.connection_manager')
            ->setPublic(true);
                
        $container->register('pmg_pheanstalk.internal.stats_service', PheanstalkStatsService::class)
            ->addArgument(new Reference('pmg_pheanstalk.internal.connection_manager'));
        $container->setAlias(StatsService::class, 'pmg_pheanstalk.internal.stats_service')
            ->setPublic(true);

        $container->register(QueueUtilities::class)
            ->addArgument(new Reference('pmg_pheanstalk.internal.connection_manager'))
            ->setPublic(true);

        $container->register(QueueController::class)
            ->addArgument(new Reference('pmg_pheanstalk.internal.stats_service'))
            ->setPublic(true);

        $container->register(StatsCommand::class)
            ->addArgument(new Reference('pmg_pheanstalk.internal.stats_service'))
            ->addTag('console.command');
        $container->register(PurgeQueueCommand::class)
            ->addArgument(new Reference(QueueUtilities::class))
            ->addTag('console.command');
    }

    private function loadConnection(ContainerBuilder $container, $name, array $config)
    {
        $name = self::serviceName($name);
        $container->register($name, Pheanstalk::class)
            ->setFactory([Pheanstalk::class, 'create'])
            ->setArguments([
                $config['host'],
                $config['port'] ?? PheanstalkInterface::DEFAULT_PORT,
                $config['timeout'] ?? 10, // what pheanstalk itself defaults to
            ])
            ->setPublic(true);

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
}
