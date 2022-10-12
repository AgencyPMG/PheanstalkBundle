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

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Pheanstalk\Contract\PheanstalkInterface;
use PMG\PheanstalkBundle\ConnectionManager;

class QueueUtilities
{
    public function __construct(private ConnectionManager $connections)
    {
    }

    public function purgeQueue(?string $connectionName, string $queueName) : int
    {
        $conn = $this->getConnection($connectionName);

        $deleteCount = 0;
        $conn->useTube($queueName);
        while ($job = $conn->peekReady()) {
            $conn->delete($job);
            ++$deleteCount;
        }

        return $deleteCount;
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
