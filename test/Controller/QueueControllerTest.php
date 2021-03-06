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

namespace PMG\PheanstalkBundle;

use LogicException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Pheanstalk\PheanstalkInterface;
use PMG\PheanstalkBundle\Controller\QueueController;
use PMG\PheanstalkBundle\Service\StatsService;

class QueueControllerTest extends TestCase
{
    private $controller, $stats;

    public function testStatsCanBeFetchedForAValidTube()
    {
        $this->willGetTubeInfo('default');

        $request = Request::create('/');

        $tubes = $this->decodeResponse($this->controller->statsTubeAction('default', $request));
        $this->assertNotNull($tubes);
        $this->assertArrayHasKey('name', $tubes);
    }

    public function testStatsCanBeFetchedForAValidTubeOnExternallyDefinedConnection()
    {
        $this->willGetTubeInfo('another');

        $request = Request::create('/');
        $request->query->set('connection', 'another');

        $tubes = $this->decodeResponse($this->controller->statsTubeAction('another', $request));
        $this->assertNotNull($tubes);
        $this->assertArrayHasKey('name', $tubes);
    }

    public function testStatsCanBeFetchedForAllTubes()
    {
        $this->willListTubeStats();

        $request = Request::create('/');

        $tubes = $this->decodeResponse($this->controller->statsTubesAction($request));

        $this->assertCount(1, $tubes);
        $this->assertArrayHasKey('default', $tubes);
    }

    public function testIfSpecifiedTubeDoesNotExistControllerWillThrowNotFoundHttpException()
    {
        $this->expectException(NotFoundHttpException::class);

        $request = Request::create('/');
        $this->stats->expects($this->once())
            ->method('getStatsForTube')
            ->with('invalid')
            ->will($this->throwException(new \Pheanstalk\Exception\ServerException('Connection does not exist')));

        $tubes = $this->controller->statsTubeAction('invalid', $request);
    }

    private function willFetchTubes()
    {
        $this->stats->expects($this->once())
            ->method('listTubes')
            ->willReturn(['default', 'test1']);
    }

    private function willGetTubeInfo($tube)
    {
        $this->stats->expects($this->once())
            ->method('getStatsForTube')
            ->with($tube)
            ->willReturn([
                'name'                => $tube,
                'current-jobs-urgent' => 0,
                'total-jobs'          => 0,
            ]);
    }

    private function willListTubeStats()
    {
        $this->stats->expects($this->once())
            ->method('listTubeStats')
            ->willReturn([
                'default' => [
                    'current-jobs-urgent' => 0,
                    'total-jobs'          => 0,
                ]
            ]);
    }

    private function decodeResponse($resp) 
    {
        return json_decode($resp->getContent(), true);
    }
    
    /**
     * This loads a container by booting up a kernel with our
     * bundle installed. The point here is that we want to get
     * as close as possible to what the actual use will be.
     */
    private function loadContainer($config)
    {
        return $this->createKernel($config)->getContainer();
    }

    protected function setUp() : void
    {
        $this->stats = $this->createMock(StatsService::class);
        $this->controller = new QueueController($this->stats);
    }
}
