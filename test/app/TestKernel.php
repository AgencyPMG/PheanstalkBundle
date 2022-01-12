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

    public function registerBundles() : array
    {
        return [
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \PMG\PheanstalkBundle\PmgPheanstalkBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader) : void
    {
        $loader->load(__DIR__.'/config/'.$this->configFile);
    }

    public function getProjectDir() : string
    {
        return __DIR__;
    }

    public function getLogDir() : string
    {
        return __DIR__.'/tmp';
    }

    public function getCacheDir() : string
    {
        return __DIR__.'/tmp';
    }
}
