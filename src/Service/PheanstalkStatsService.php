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

use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Pheanstalk\Contract\PheanstalkInterface;
use PMG\PheanstalkBundle\ConnectionManager;

class PheanstalkStatsService implements StatsService
{
    private $connections;

    public function __construct(ConnectionManager $connections)
    {
        $this->connections = $connections;
    }

    /**
     * {@inheritdoc}
     */
    public function serverStats(?string $connection)
    {
        return $this->getConnection($connection)->stats();
    }

    /**
     * {@inheritdoc}
     */
    public function listTubes(?string $connection)
    {
        return $this->getConnection($connection)->listTubes();
    }

    /**
     * {@inheritdoc}
     */
    public function getStatsForTube(string $tube, ?string $connection)
    {
        return (array) $this->getConnection($connection)->statsTube($tube);
    }

    /**
     * {@inheritdoc}
     */
    public function listTubeStats(?string $connection)
    {
        $tubes = $this->getConnection($connection)->listTubes();

        $stats = [];
        foreach($tubes as $tube) {
            $stat = $this->getStatsForTube($tube, $connection);
            unset($stat['name']);
            $stats[$tube] = $stat;
        }

        return $stats;
    }

    /**
     * Attempts to get a valid Pheanstalk connection
     *
     * @return Pheanstalk\Contract\PheanstalkInterface
     */
    private function getConnection(?string $connName) : PheanstalkInterface
    {
        return $connName ? $this->connections->get($connName) : $this->connections->getDefault();
    }
}
