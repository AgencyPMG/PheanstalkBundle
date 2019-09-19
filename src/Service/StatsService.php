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

interface StatsService
{
    /**
     * Get the server stats.
     *
     * @return string[]
     */
    public function serverStats(?string $connection);

    /**
     * Returns all tubes available for the connection
     *
     * @param $connection the connection for which tubes should be listed
     * @return String[]
     */
    public function listTubes(?string $connection);

    /**
     * Returns stats for a given tube
     *
     * @return String[]
     */
    public function getStatsForTube(string $tube, ?string $connection);

    /**
     * Returns stats for all tubes on the given connection
     *
     * @return String[]
     */
    public function listTubeStats(?string $connection);
}
