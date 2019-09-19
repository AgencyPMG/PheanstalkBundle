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

class StatsCommandTest extends \PMG\PheanstalkBundle\TestCase
{
    public function testStatsCommandWithoutArgumentsFetchesAllServerStatus()
    {
        $tester = $this->createConsole();

        $this->assertEquals(0, $tester->run(['pheanstalk:stats']));
        $this->assertStringContainsStringIgnoringCase('Server Stats', $tester->getDisplay());
    }

    public function testStatsCanBeFetchedWithNamedConnection()
    {
        $tester = $this->createConsole();

        $this->assertEquals(0, $tester->run(['pheanstalk:stats', '-c' => 'default']));
        $this->assertStringContainsStringIgnoringCase('Server Stats', $tester->getDisplay());
    }

    public function testInvalidNamedConnectionCausesError()
    {
        $tester = $this->createConsole();

        $this->assertGreaterThan(0, $tester->run(['pheanstalk:stats', '-c' => 'doesNotExist']));
        $this->assertStringContainsStringIgnoringCase('No Pheanstalk connection named', $tester->getDisplay());
    }

    public function testStatsWithNonExistentTubeReportsError()
    {
        $tester = $this->createConsole();

        $tube = uniqid('pmgPheanstalk');
        $this->assertEquals(0, $tester->run(['pheanstalk:stats', 'tube' => [$tube]]));
        $this->assertStringContainsStringIgnoringCase(sprintf('Tube "%s" not found', $tube), $tester->getDisplay());
    }

    public function testStatsForExistingTubePrintsTubeStats()
    {
        $tester = $this->createConsole();

        $this->assertEquals(0, $tester->run(['pheanstalk:stats', 'tube' => ['default']]));
        $this->assertStringContainsStringIgnoringCase('default Stats', $tester->getDisplay());
    }

    public function testInvalidConnectionCausesError()
    {
        $kernel = $this->createKernel('badconn.yml');
        $console = new Application($kernel);
        $console->setAutoExit(false);
        $tester = new ApplicationTester($console);

        $this->assertGreaterThan(0, $tester->run(['pheanstalk:stats', 'tube' => ['default']]));
        $this->assertStringContainsStringIgnoringCase('Pheanstalk\\Exception', $tester->getDisplay());
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
