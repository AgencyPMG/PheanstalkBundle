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

class PheanstalkStatsService implements StatsService
{
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function listTubes($connection)
    {
        return $conn->listTubes();
    }

    /**
     * {@inheritdoc}
     */
    public function getStatsForTube($tube, $connection)
    {
        return (array) $conn->statsTube($tube);
    }

    /**
     * {@inheritdoc}
     */
    public function listTubeStats($connection)
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

    /**
     * Attempts to get a valid Pheanstalk connection
     *
     * @return \Pheanstalk\PheanstalkInterface
     */
    private function getConnection($connName=null)
    {
        $connName = $connName ?: $this->container->getParameter('pmg_pheanstalk.params.default_conn');

        $valid = $this->container->getParameter('pmg_pheanstalk.params.connections');
        if (!isset($valid[$connName])) {
            throw new BadRequestHttpException(sprintf("{$connName} is not a valid Pheanstalk connection", $connName));
        }

        return $this->container->get("pmg_pheanstalk.{$connName}");
    }
}
