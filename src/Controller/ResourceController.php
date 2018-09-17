<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\ApiBundle\Controller;


use HeimrichHannot\ApiBundle\ApiResource\ResourceInterface;
use HeimrichHannot\ApiBundle\Exception\ResourceNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api",defaults={"_scope"="api","_token_check"=false})
 */
class ResourceController extends Controller
{
    /**
     * @return Response
     *
     * @param string $alias
     *
     * @Route("/resource/{alias}", name="api_resource_create", methods={"POST"})
     */
    public function createAction(string $alias, Request $request)
    {
        /** @var ResourceInterface $resource */
        if (null === ($resource = $this->container->get('huh.api.manager.resource')->get($alias))) {
            throw new ResourceNotFoundException('huh.api.exception.resource_not_found');
        }

        return new JsonResponse($resource->create($request, $this->getUser()));
    }

    /**
     * @return Response
     *
     * @param string $alias
     * @param mixed  $id Entity id
     *
     * @Route("/resource/{alias}/{id}", name="api_resource_update", methods={"PUT"})
     */
    public function updateAction($id, string $alias, Request $request)
    {
        /** @var ResourceInterface $resource */
        if (null === ($resource = $this->container->get('huh.api.manager.resource')->get($alias))) {
            throw new ResourceNotFoundException('huh.api.exception.resource_not_found');
        }

        return new JsonResponse($resource->update($id, $request, $this->getUser()));
    }

    /**
     * @return Response
     *
     * @param string $alias
     *
     * @Route("/resource/{alias}", name="api_resource_list", methods={"GET"})
     */
    public function listAction(string $alias, Request $request)
    {
        /** @var ResourceInterface $resource */
        if (null === ($resource = $this->container->get('huh.api.manager.resource')->get($alias))) {
            throw new ResourceNotFoundException('huh.api.exception.resource_not_found');
        }

        return new JsonResponse($resource->list($request, $this->getUser()));
    }

    /**
     * @return Response
     *
     * @param string $alias
     * @param mixed  $id Entity id
     *
     * @Route("/resource/{alias}/{id}", name="api_resource_show", methods={"GET"})
     */
    public function showAction(string $alias, $id, Request $request)
    {
        /** @var ResourceInterface $resource */
        if (null === ($resource = $this->container->get('huh.api.manager.resource')->get($alias))) {
            throw new ResourceNotFoundException('huh.api.exception.resource_not_found');
        }

        return new JsonResponse($resource->show($id, $request, $this->getUser()));
    }

    /**
     * @return Response
     *
     * @param string $alias
     * @param mixed  $id Entity id
     *
     * @Route("/resource/{alias}/{id}", name="api_resource_delete", methods={"DELETE"})
     */
    public function deleteAction(string $alias, $id, Request $request)
    {
        /** @var ResourceInterface $resource */
        if (null === ($resource = $this->container->get('huh.api.manager.resource')->get($alias))) {
            throw new ResourceNotFoundException('huh.api.exception.resource_not_found');
        }

        return new JsonResponse($resource->delete($id, $request, $this->getUser()));
    }
}