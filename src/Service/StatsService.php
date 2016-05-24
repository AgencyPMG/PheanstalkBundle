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

interface StatsService
{
    /**
     * Returns all tubes available for the connection
     *
     * @param PheanstalkInterface
     * @return String[]
     */
    public function listTubes(PheanstalkInterface $conn);

    /**
     * Returns stats for a given tube
     *
     * @param $tube string - the name of the tube
     * @param PheanstalkInterface
     * @return String[]
     */
    public function getStatsForTube($tube, PheanstalkInterface $conn);

    /**
     * Returns stats for all tubes on the given connection
     *
     * @param PheanstalkInterface
     * @return String[]
     */
    public function listTubeStats(PheanstalkInterface $conn);
}
