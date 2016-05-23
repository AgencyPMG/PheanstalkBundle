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
use PMG\PheanstalkBundle\Controller\Exception\InvalidTube;

class QueueController extends AbstractController
{
    public function listTubesAction(Request $r)
    {
        return $this->toResponse($this->getConnection($r)->listTubes());
    }

    public function getInfoAction($tube, Request $r)
    {
        $conn = $this->getConnection($r);
        $tubes = $conn->listTubes();

        if (!in_array($tube, $tubes)) {
            throw new InvalidTube(sprintf('%s is not a valid tube', $tube));
        }

        return $this->toResponse((array) $conn->statsTube($tube));
    }

    public function listInfoAction(Request $r)
    {
        $conn = $this->getConnection($r);
        $tubes = $conn->listTubes();

        $stats = [];
        foreach($tubes as $tube) {
            $stat = (array) $conn->statsTube($tube);
            unset($stat['name']);
            $stats[$tube] = $stat;
        }

        return $this->toResponse($stats);
    }
}
