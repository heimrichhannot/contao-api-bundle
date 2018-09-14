<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\ApiBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/api",defaults={"_scope"="api","_token_check"=false})
 */
class ApiResourceController extends Controller
{
    /**
     * @return Response
     *
     * @Route("/resource/{resource}", name="resource_add")
     * @Method({"POST"})
     */
    public function createAction(string $resource, Request $request)
    {
        return new JsonResponse(['Action' => 'create ' . $resource]);
    }

    /**
     * @return Response
     *
     * @Route("/resource/{resource}", name="resource_add")
     * @Method({"GET"})
     */
    public function listAction(string $resource, Request $request)
    {
        return new JsonResponse(['Action' => 'list ' . $resource]);
    }
}