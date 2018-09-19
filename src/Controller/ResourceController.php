<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ApiBundle\Controller;

use Contao\StringUtil;
use HeimrichHannot\ApiBundle\ApiResource\ResourceInterface;
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
     * @param string $alias
     *
     * @return Response
     *
     * @Route("/resource/{alias}", name="api_resource_create", methods={"POST"})
     */
    public function createAction(string $alias, Request $request)
    {
        /** @var ResourceInterface $resource */
        if (null === ($resource = $this->container->get('huh.api.manager.resource')->get($alias))) {
            return new JsonResponse(['message' => $this->container->get('translator')->trans('huh.api.exception.resource_not_existing', ['%alias' => $alias])]);
        }

        if (false === $this->isActionAllowed($request)) {
            return new JsonResponse(['message' => $this->container->get('translator')->trans('huh.api.exception.resource_action_not_allowed', ['%resource%' => $alias, '%action%' => $request->attributes->get('_route')])]);
        }

        return new JsonResponse($resource->create($request, $this->getUser()));
    }

    /**
     * @param string $alias
     * @param mixed  $id    Entity id
     *
     * @return Response
     *
     * @Route("/resource/{alias}/{id}", name="api_resource_update", methods={"PUT"})
     */
    public function updateAction(string $alias, $id, Request $request)
    {
        /** @var ResourceInterface $resource */
        if (null === ($resource = $this->container->get('huh.api.manager.resource')->get($alias))) {
            return new JsonResponse(['message' => $this->container->get('translator')->trans('huh.api.exception.resource_not_existing', ['%alias' => $alias])]);
        }

        if (false === $this->isActionAllowed($request)) {
            return new JsonResponse(['message' => $this->container->get('translator')->trans('huh.api.exception.resource_action_not_allowed', ['%resource%' => $alias, '%action%' => $request->attributes->get('_route')])]);
        }

        return new JsonResponse($resource->update($id, $request, $this->getUser()));
    }

    /**
     * @param string $alias
     *
     * @return Response
     *
     * @Route("/resource/{alias}", name="api_resource_list", methods={"GET"})
     */
    public function listAction(string $alias, Request $request)
    {
        /** @var ResourceInterface $resource */
        if (null === ($resource = $this->container->get('huh.api.manager.resource')->get($alias))) {
            return new JsonResponse(['message' => $this->container->get('translator')->trans('huh.api.exception.resource_not_existing', ['%alias' => $alias])]);
        }

        if (false === $this->isActionAllowed($request)) {
            return new JsonResponse(['message' => $this->container->get('translator')->trans('huh.api.exception.resource_action_not_allowed', ['%resource%' => $alias, '%action%' => $request->attributes->get('_route')])]);
        }

        return new JsonResponse($resource->list($request, $this->getUser()));
    }

    /**
     * @param string $alias
     * @param mixed  $id    Entity id
     *
     * @return Response
     *
     * @Route("/resource/{alias}/{id}", name="api_resource_show", methods={"GET"})
     */
    public function showAction(string $alias, $id, Request $request)
    {
        /** @var ResourceInterface $resource */
        if (null === ($resource = $this->container->get('huh.api.manager.resource')->get($alias))) {
            return new JsonResponse(['message' => $this->container->get('translator')->trans('huh.api.exception.resource_not_existing', ['%alias' => $alias])]);
        }

        if (false === $this->isActionAllowed($request)) {
            return new JsonResponse(['message' => $this->container->get('translator')->trans('huh.api.exception.resource_action_not_allowed', ['%resource%' => $alias, '%action%' => $request->attributes->get('_route')])]);
        }

        return new JsonResponse($resource->show($id, $request, $this->getUser()));
    }

    /**
     * @param string $alias
     * @param mixed  $id    Entity id
     *
     * @return Response
     *
     * @Route("/resource/{alias}/{id}", name="api_resource_delete", methods={"DELETE"})
     */
    public function deleteAction(string $alias, $id, Request $request)
    {
        /** @var ResourceInterface $resource */
        if (null === ($resource = $this->container->get('huh.api.manager.resource')->get($alias))) {
            return new JsonResponse(['message' => $this->container->get('translator')->trans('huh.api.exception.resource_not_existing', ['%alias' => $alias])]);
        }

        if (false === $this->isActionAllowed($request)) {
            return new JsonResponse(['message' => $this->container->get('translator')->trans('huh.api.exception.resource_action_not_allowed', ['%resource%' => $alias, '%action%' => $request->attributes->get('_route')])]);
        }

        return new JsonResponse($resource->delete($id, $request, $this->getUser()));
    }

    /**
     * Determine if action is allowed.
     *
     * @param Request $request
     *
     * @return bool
     */
    protected function isActionAllowed(Request $request): bool
    {
        if (null === ($api = $this->getUser()->getApp())) {
            return false;
        }

        $allowed = StringUtil::deserialize($this->getUser()->getApp()->resourceActions, true);

        if (empty($allowed)) {
            return false;
        }

        if (!\in_array($request->attributes->get('_route'), $allowed)) {
            return false;
        }

        return true;
    }
}
