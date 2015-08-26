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

use PMG\PheanstalkBundle\Test\TestKernel;

abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    private $kernels = [];

    protected function tearDown()
    {
        foreach ($this->kernels as $k) {
            $k->shutdown();
        }

        // clear up the cached files
        foreach (glob(__DIR__.'/app/tmp/*') as $fn) {
            if ('.' !== basename($fn)[0]) {
                @unlink($fn);
            }
        }
    }

    protected function createKernel($config)
    {
        // "unique" environment here to get around caching issues
        $kernel = new TestKernel($config, uniqid('pmgpheanstalk'));
        $kernel->boot();
        $this->kernels[] = $kernel;

        return $kernel;
    }
}
