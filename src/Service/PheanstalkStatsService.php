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

use Pheanstalk\PheanstalkInterface;

class PheanstalkStatsService implements StatsService
{
    /**
     * {@inheritdoc}
     */
    public function listTubes(PheanstalkInterface $conn)
    {
        return $conn->listTubes();
    }

    /**
     * {@inheritdoc}
     */
    public function getStatsForTube($tube, PheanstalkInterface $conn)
    {
        return (array) $conn->statsTube($tube);
    }

    /**
     * {@inheritdoc}
     */
    public function listTubeStats(PheanstalkInterface $conn)
    {
        $tubes = $conn->listTubes();

        $stats = [];
        foreach($tubes as $tube) {
            $stat = $this->getStatsForTube($tube, $conn);
            unset($stat['name']);
            $stats[$tube] = $stat;
        }

        return $stats;
    }
}
