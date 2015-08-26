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

class StatsCommandTest extends \PMG\PheanstalkBundle\TestCase
{
    public function testStatsCommandWithoutArgumentsFetchesAllServerStatus()
    {
        $tester = $this->createConsole();

        $this->assertEquals(0, $tester->run(['pheanstalk:stats']));
        $this->assertContains('Server Stats', $tester->getDisplay());
    }

    public function testStatsCanBeFetchedWithNamedConnection()
    {
        $tester = $this->createConsole();

        $this->assertEquals(0, $tester->run(['pheanstalk:stats', '-c' => 'default']));
        $this->assertContains('Server Stats', $tester->getDisplay());
    }

    public function testInvalidNamedConnectionCausesError()
    {
        $tester = $this->createConsole();

        $this->assertGreaterThan(0, $tester->run(['pheanstalk:stats', '-c' => 'doesNotExist']));
        $this->assertContains('No Pheanstalk connection named', $tester->getDisplay());
    }

    public function testStatsWithNonExistentTubeReportsError()
    {
        $tester = $this->createConsole();

        $tube = uniqid('pmgPheanstalk');
        $this->assertEquals(0, $tester->run(['pheanstalk:stats', 'tube' => [$tube]]));
        $this->assertContains(sprintf('Tube "%s" not found', $tube), $tester->getDisplay());
    }

    public function testStatsForExistingTubePrintsTubeStats()
    {
        $tester = $this->createConsole();

        $this->assertEquals(0, $tester->run(['pheanstalk:stats', 'tube' => ['default']]));
        $this->assertContains('default Stats', $tester->getDisplay());
    }

    public function testInvalidConnectionCausesError()
    {
        $kernel = $this->createKernel('badconn.yml');
        $console = new Application($kernel);
        $console->setAutoExit(false);
        $tester = new ApplicationTester($console);

        $this->assertGreaterThan(0, $tester->run(['pheanstalk:stats', 'tube' => ['default']]));
        $this->assertContains('Pheanstalk\\Exception', $tester->getDisplay());
    }

    private function createConsole()
    {
        $kernel = $this->createKernel('default.yml');

        if (!$kernel->getContainer()->get('pmg_pheanstalk')->getConnection()->isServiceListening()) {
            return $this->markTestSkipped('Beanstalkd is Not Running');
        }

        $console = new Application($kernel);
        $console->setAutoExit(false);

        return new ApplicationTester($console);
    }
}
