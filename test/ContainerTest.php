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

namespace PMG\PheanstalkBundle;

use LogicException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Pheanstalk\Contract\PheanstalkInterface;

class ContainerTest extends TestCase
{
    private $kernels = [];

    public function testDefaultConfigurationLoadsOneConnection()
    {
        $container = $this->loadContainer('default.yml');

        $this->assertCount(1, $container->get(ConnectionManager::class));
        $this->assertPheanstalk($container->get('pmg_pheanstalk.default'), 'should have the default connection def');
        $this->assertPheanstalk($container->get('pmg_pheanstalk'), 'should have the default alias');
        $this->assertSame($container->get('pmg_pheanstalk.default'), $container->get('pmg_pheanstalk'));
        $this->assertSame($container->get('pmg_pheanstalk.default'), $container->get(PheanstalkInterface::class));
    }

    public function testConfigurationCanLoadMultipleDefaultConnections()
    {
        $container = $this->loadContainer('multiple_default.yml');

        $this->assertCount(2, $container->get(ConnectionManager::class));
        $this->assertPheanstalk($container->get('pmg_pheanstalk.default'), 'should have the "default" connection def');
        $this->assertPheanstalk($container->get('pmg_pheanstalk.another'), 'should have the "another" connection def');
        $this->assertPheanstalk($container->get('pmg_pheanstalk'), 'should have the default alias');
        $this->assertSame($container->get('pmg_pheanstalk.default'), $container->get('pmg_pheanstalk'));
        $this->assertSame($container->get('pmg_pheanstalk.default'), $container->get(PheanstalkInterface::class));
    }

    public function testConfigurationWithDifferentDefaultConnectionCanBeLoaded()
    {
        $container = $this->loadContainer('altdefault.yml');

        $this->assertCount(1, $container->get(ConnectionManager::class));
        $this->assertPheanstalk($container->get('pmg_pheanstalk.another'), 'should have the "another" connection def');
        $this->assertPheanstalk($container->get('pmg_pheanstalk'), 'should have the default alias');
        $this->assertSame($container->get('pmg_pheanstalk.another'), $container->get('pmg_pheanstalk'));
        $this->assertSame($container->get('pmg_pheanstalk.another'), $container->get(PheanstalkInterface::class));
    }

    public function testConfigurationWithUnrecognizedDefaultValueCausesError()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('No Pheanstalk connection named');

        $this->loadContainer('baddefault.yml');
    }

    /**
     * This loads a container by booting up a kernel with our
     * bundle installed. The point here is that we want to get
     * as close as possible to what the actual use will be.
     */
    private function loadContainer($config)
    {
        return $this->createKernel($config)->getContainer();
    }

    private function assertPheanstalk($obj, $msg='')
    {
        $this->assertInstanceOf(PheanstalkInterface::class, $obj, $msg);
    }
}
