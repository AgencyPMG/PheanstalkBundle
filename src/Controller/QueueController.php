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
use Symfony\Component\HttpFoundation\JsonResponse;
use PMG\PheanstalkBundle\Controller\Exception\InvalidTube;
use PMG\PheanstalkBundle\Service\StatsService;

class QueueController extends Controller
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
        $conn = $this->getConnectionFromRequest($r);
        return $this->toResponse($this->stats->listTubes($conn));
    }

    public function statsTubeAction($tube, Request $r)
    {
        $conn = $this->getConnectionFromRequest($r);

        try {
            return $this->toResponse($this->stats->getStatsForTube($tube, $conn));
        } catch (\Pheanstalk\Exception\ServerException $e) {
            throw new NotFoundHttpException(sprintf('Tube %s Not Found', $tube), $e);
        }
    }

    public function statsTubesAction(Request $r)
    {
        $conn = $this->getConnectionFromRequest($r);
        return $this->toResponse($this->stats->listTubeStats($conn));
    }

    private function getConnectionFromRequest(Request $r)
    {
        return $r->get('connection');
    }

    /**
     * Creates a json response from an object.
     *
     * @param $data - the object to transform
     * @param $statusCode (optional) - the status code from the request
     * @param $headers String[] (optional) - a list of headers to include with the response
     * @return JsonResponse
     */
    protected function toResponse($data, $statusCode=null, array $headers=[])
    {
        return new JsonResponse($data, $statusCode ?: 200, $headers);
    }
}
