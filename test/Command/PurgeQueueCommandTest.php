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

namespace PMG\PheanstalkBundle\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\ApplicationTester;
use Pheanstalk\Exception as PheanstalkException;

class PurgeQueueCommandTest extends \PMG\PheanstalkBundle\TestCase
{
    public function testCommandCanPurgeQueueOnDefaultConnection()
    {
        $tester = $this->createConsole();

        $exitCode = $tester->run([
            'pheanstalk:purge-queue',
            'queue' => __FUNCTION__,
        ]);

        $this->assertSame(0, $exitCode, $tester->getDisplay());
        $this->assertStringContainsStringIgnoringCase(
            'removed 0 jobs from '.__FUNCTION__,
            $tester->getDisplay(),
        );
    }

    private function createConsole()
    {
        $kernel = $this->createKernel('default.yml');

        try {
            $kernel->getContainer()->get('pmg_pheanstalk')->stats();
        } catch (PheanstalkException $e) {
            $this->markTestSkipped('Beanstalkd is Not Running: '.$e->getMessage());
            return;
        }

        $console = new Application($kernel);
        $console->setAutoExit(false);

        return new ApplicationTester($console);
    }
}
