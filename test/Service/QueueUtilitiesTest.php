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

namespace PMG\PheanstalkBundle\Service;

use Pheanstalk\Contract\PheanstalkInterface;
use PMG\PheanstalkBundle\ConnectionManager;
use PMG\PheanstalkBundle\TestCase;

class QueueUtilitiesTest extends TestCase
{
    const DEFAULT_CONN = 'default';
    const NAMED_CONN = 'test';

    private PheanstalkInterface $defaultConn;
    private PheanstalkInterface $namedConn;
    private ConnectionManager $connections;
    private QueueUtilities $queueUtilities;

    /**
     * @return iterable<string, array<string|null>>
     */
    public static function connectionNames() : iterable
    {
        yield 'default' => [null];
        yield 'named' => [self::NAMED_CONN];
    }

    /**
     * @dataProvider connectionNames
     */
    public function testPurgeQueueWithNoJobsReturnsEmptyQueue(?string $connName) : void
    {
        $result = $this->queueUtilities->purgeQueue($connName, __FUNCTION__);

        $this->assertSame(0, $result);
    }

    /**
     * @dataProvider connectionNames
     */
    public function testPurgeQueueRemovesJobsFromQueue(?string $connName) : void
    {
        $queue = uniqid('queueutilstest_');
        $conn = $connName ? $this->connections->get($connName) : $this->defaultConn;
        foreach (range(1, 10) as $i) {
            $conn->useTube($queue)->put("job_{$i}");
        }

        $result = $this->queueUtilities->purgeQueue($connName, $queue);

        $this->assertSame(10, $result);
        $this->assertNull($conn->peekReady(), 'should not be any jobs left');
    }

    protected function setUp() : void
    {
        $this->defaultConn = $this->createPheanstalk();
        $this->namedConn = $this->createPheanstalk();
        $this->connections = new ConnectionManager([
            self::NAMED_CONN => $this->namedConn,
            self::DEFAULT_CONN => $this->defaultConn,
        ], self::DEFAULT_CONN);
        $this->queueUtilities = new QueueUtilities($this->connections);
    }
}
