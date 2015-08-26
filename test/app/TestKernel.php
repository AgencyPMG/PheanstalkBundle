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

namespace PMG\PheanstalkBundle\Test;

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

final class TestKernel extends Kernel
{
    private $configFile;

    public function __construct($configFile=null, $env='test')
    {
        $this->configFile = $configFile ?: 'default.yml';
        parent::__construct($env, true);
    }

    public function registerBundles()
    {
        $bundles = array(
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \PMG\PheanstalkBundle\PmgPheanstalkBundle(),
        );

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir()."/config/{$this->configFile}");
    }

    public function getLogDir()
    {
        return __DIR__.'/tmp';
    }

    public function getCacheDir()
    {
        return __DIR__.'/tmp';
    }
}
