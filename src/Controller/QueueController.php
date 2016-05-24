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

namespace PMG\PheanstalkBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use PMG\PheanstalkBundle\Controller\Exception\InvalidTube;
use PMG\PheanstalkBundle\Service\StatsService;

class QueueController extends AbstractController
{
    /**
     * @var PheanstalkStats
     */
    private $stats;

    public function __construct(StatsService $stats)
    {
        $this->stats = $stats;
    }

    public function listTubesAction(Request $r)
    {
        $conn = $this->getConnection($r);
        return $this->toResponse($this->stats->listTubes($conn));
    }

    public function statsTubeAction($tube, Request $r)
    {
        $conn = $this->getConnection($r);

        try {
            return $this->toResponse($this->stats->getStatsForTube($tube, $conn));
        } catch (\Pheanstalk\Exception\ServerException $e) {
            throw new NotFoundHttpException(sprintf('Tube %s Not Found', $tube), $e);
        }
    }

    public function statsTubesAction(Request $r)
    {
        $conn = $this->getConnection($r);
        return $this->toResponse($this->stats->listTubeStats($conn));
    }
}
