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
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use PMG\PheanstalkBundle\Controller\Exception\BadConnection;

abstract class AbstractController extends Controller
{
    /**
     * Attempts to get a valid Pheanstalk connection
     *
     * @return \Pheanstalk\PheanstalkInterface
     */
    public function getConnection(Request $request)
    {
        $connName = $request->get('connection') ?: $this->getParameter('pmg_pheanstalk.params.default_conn');

        $valid = $this->getParameter('pmg_pheanstalk.params.connections');
        if (!isset($valid[$connName])) {
            throw new HttpException(400, sprintf("{$connName} is not a valid Pheanstalk connection", $connName));
        }

        return $this->get("pmg_pheanstalk.{$connName}");
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
