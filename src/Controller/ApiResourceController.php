<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\ApiBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api",defaults={"_scope"="api","_token_check"=false})
 */
class ApiResourceController extends Controller
{
    /**
     * @return Response
     *
     * @param string $resource
     *
     * @Route("/resource/{resource}", name="resource_add", methods={"POST"})
     */
    public function createAction(string $resource, Request $request)
    {
        return new JsonResponse(['Action' => 'create '.$resource]);
    }

    /**
     * @return Response
     *
     * @param string $resource
     *
     * @Route("/resource/{resource}", name="resource_list", methods={"GET"})
     */
    public function listAction(string $resource, Request $request)
    {
        return new JsonResponse(['Action' => 'list '.$resource]);
    }

    /**
     * @return Response
     *
     * @param string $resource
     * @param mixed  $id Entity id
     *
     * @Route("/resource/{resource}/{id}", name="resource_show", methods={"GET"})
     */
    public function showAction(string $resource, $id, Request $request)
    {
        return new JsonResponse(['Action' => 'show '.$resource]);
    }

    /**
     * @return Response
     *
     * @param string $resource
     * @param mixed  $id Entity id
     *
     * @Route("/resource/{resource}/{id}", name="resource_delete", methods={"DELETE"})
     */
    public function deleteAction(string $resource, $id, Request $request)
    {
        return new JsonResponse(['Action' => 'delete '.$resource]);
    }
}