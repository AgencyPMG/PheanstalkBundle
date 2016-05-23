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

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\Request;
use Pheanstalk\PheanstalkInterface;
use PMG\PheanstalkBundle\Controller\QueueController;

class QueueControllerTest extends TestCase
{
    public function testControllerCanFetchAValidConnection()
    {
        $container = $this->loadContainer('default.yml');
        $request = $this->getMock(Request::class);
        $this->controller->setContainer($container);

        $conn = $this->controller->getConnection($request);
        $this->assertNotNull($conn);
        $this->assertInstanceOf(PheanstalkInterface::class, $conn);
    }

    public function testTubesCanBeListedSuccessfully()
    {
        $container = $this->loadContainer('default.yml');
        $request = $this->getMock(Request::class);
        $this->controller->setContainer($container);

        $tubes = $this->controller->listTubesAction($request);
    }

    public function testStatsCanBeFetchedForAValidTube()
    {
        $container = $this->loadContainer('default.yml');
        $request = $this->getMock(Request::class);
        $this->controller->setContainer($container);

        $tubes = $this->decodeResponse($this->controller->getInfoAction('default', $request));
        $this->assertNotNull($tubes);
        $this->assertArrayHasKey('name', $tubes);
    }

    /**
     * @expectedException PMG\PheanstalkBundle\Controller\Exception\InvalidTube
     */
    public function testGetInfoWillThrowExceptionWhenInvalidTubeIsSpecified()
    {
        $container = $this->loadContainer('default.yml');
        $request = $this->getMock(Request::class);
        $this->controller->setContainer($container);
        $this->controller->getInfoAction('invalid', $request);
    }

    public function testStatsCanBeFetchedForAllTubes()
    {
        $container = $this->loadContainer('default.yml');
        $request = $this->getMock(Request::class);
        $this->controller->setContainer($container);

        $tubes = $this->decodeResponse($this->controller->listInfoAction($request));

        $this->assertCount(1, $tubes);
        $this->assertArrayHasKey('default', $tubes);
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

    protected function setUp()
    {
        $this->controller = new QueueController();
    }
}
