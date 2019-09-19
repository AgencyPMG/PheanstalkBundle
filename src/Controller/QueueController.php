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

use LogicException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use PMG\PheanstalkBundle\Service\StatsService;

class QueueController
{
    /**
     * @var PheanstalkStats
     */
    private $stats;

    public function __construct(StatsService $stats)
    {
        $this->stats = $stats;
    }

    public function statsTubeAction($tube, Request $r)
    {
        $conn = $this->getConnectionFromRequest($r);

        try {
            return $this->toResponse($this->stats->getStatsForTube($tube, $conn));
        } catch (LogicException $e) {
            throw new BadRequestHttpException($e->getMesasge(), $e);
        } catch (\Pheanstalk\Exception\ServerException $e) {
            throw new NotFoundHttpException(sprintf('Tube %s Not Found', $tube), $e);
        }
    }

    public function statsTubesAction(Request $r)
    {
        $conn = $this->getConnectionFromRequest($r);
        try {
            return $this->toResponse($this->stats->listTubeStats($conn));
        } catch (LogicException $e) {
            throw new BadRequestHttpException($e->getMesasge(), $e);
        }
    }

    private function getConnectionFromRequest(Request $r) : ?string
    {
        $conn = $r->query->get('connection');
        if (!$conn) {
            return null;
        }

        if (!is_string($conn)) {
            throw new BadRequestHttpException('`connection` must be a string');
        }

        return $conn;
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
