<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ApiBundle\Controller;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\StringUtil;
use HeimrichHannot\ApiBundle\ApiResource\ResourceInterface;
use HeimrichHannot\ApiBundle\Manager\ApiResourceManager;
use HeimrichHannot\ApiBundle\Model\ApiAppActionModel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api",defaults={"_scope"="api","_token_check"=false})
 */
class ResourceController extends AbstractController
{
    /**
     * @return Response
     *
     * @Route("/resource/{alias}", name="api_resource_create", methods={"POST"})
     */
    public function createAction(string $alias, Request $request)
    {
        /** @var ResourceInterface $resource */
        if (null === ($resource = $this->container->get('huh.api.manager.resource')->get($alias))) {
            return $this->json(['message' => $this->container->get('translator')->trans('huh.api.exception.resource_not_existing', ['%alias%' => $alias])]);
        }

        if (false === $this->isActionAllowed($request)) {
            return $this->json(['message' => $this->container->get('translator')->trans('huh.api.exception.resource_action_not_allowed', ['%resource%' => $alias, '%action%' => $request->attributes->get('_route')])]);
        }

        return $this->json($resource->create($request, $this->getUser()));
    }

    /**
     * @param mixed $id Entity id
     *
     * @return Response
     *
     * @Route("/resource/{alias}/{id}", name="api_resource_update", methods={"PUT"})
     */
    public function updateAction(string $alias, $id, Request $request)
    {
        /** @var ResourceInterface $resource */
        if (null === ($resource = $this->container->get('huh.api.manager.resource')->get($alias))) {
            return $this->json(['message' => $this->container->get('translator')->trans('huh.api.exception.resource_not_existing', ['%alias%' => $alias])]);
        }

        if (false === $this->isActionAllowed($request)) {
            return $this->json(['message' => $this->container->get('translator')->trans('huh.api.exception.resource_action_not_allowed', ['%resource%' => $alias, '%action%' => $request->attributes->get('_route')])]);
        }

        return $this->json($resource->update($id, $request, $this->getUser()));
    }

    /**
     * @return Response
     *
     * @Route("/resource/{alias}", name="api_resource_list", methods={"GET"})
     */
    public function listAction(string $alias, Request $request)
    {
        $this->container->get('contao.framework')->initialize();

        /** @var ResourceInterface $resource */
        if (null === ($resource = $this->container->get('huh.api.manager.resource')->get($alias))) {
            return $this->json(['message' => $this->container->get('translator')->trans('huh.api.exception.resource_not_existing', ['%alias%' => $alias])]);
        }

        if (false === $this->isActionAllowed($request)) {
            return $this->json(['message' => $this->container->get('translator')->trans('huh.api.exception.resource_action_not_allowed', ['%resource%' => $alias, '%action%' => $request->attributes->get('_route')])]);
        }

        return $this->json($resource->list($request, $this->getUser()));
    }

    /**
     * @param mixed $id Entity id
     *
     * @return Response
     *
     * @Route("/resource/{alias}/{id}", name="api_resource_show", methods={"GET"})
     */
    public function showAction(string $alias, $id, Request $request)
    {
        /** @var ResourceInterface $resource */
        if (null === ($resource = $this->container->get('huh.api.manager.resource')->get($alias))) {
            return $this->json(['message' => $this->container->get('translator')->trans('huh.api.exception.resource_not_existing', ['%alias%' => $alias])]);
        }

        if (false === $this->isActionAllowed($request)) {
            return $this->json(['message' => $this->container->get('translator')->trans('huh.api.exception.resource_action_not_allowed', ['%resource%' => $alias, '%action%' => $request->attributes->get('_route')])]);
        }

        return $this->json($resource->show($id, $request, $this->getUser()));
    }

    /**
     * @param mixed $id Entity id
     *
     * @return Response
     *
     * @Route("/resource/{alias}/{id}", name="api_resource_delete", methods={"DELETE"})
     */
    public function deleteAction(string $alias, $id, Request $request)
    {
        /** @var ResourceInterface $resource */
        if (null === ($resource = $this->container->get('huh.api.manager.resource')->get($alias))) {
            return $this->json(['message' => $this->container->get('translator')->trans('huh.api.exception.resource_not_existing', ['%alias%' => $alias])]);
        }

        if (false === $this->isActionAllowed($request)) {
            return $this->json(['message' => $this->container->get('translator')->trans('huh.api.exception.resource_action_not_allowed', ['%resource%' => $alias, '%action%' => $request->attributes->get('_route')])]);
        }

        return $this->json($resource->delete($id, $request, $this->getUser()));
    }

    public static function getSubscribedServices()
    {
        $services = parent::getSubscribedServices();
        $services['huh.api.manager.resource'] = ApiResourceManager::class;
        $services['contao.framework'] = ContaoFramework::class;

        return $services;
    }

    /**
     * Determine if action is allowed.
     */
    protected function isActionAllowed(Request $request): bool
    {
        if (null === ($app = $this->getUser()->getApp())) {
            return false;
        }

        $resourceManager = $this->container->get('huh.api.manager.resource');

        switch ($app->type) {
            case $resourceManager::TYPE_ENTITY_RESOURCE:
                if (null === ($action = ApiAppActionModel::findOneBy(['tl_api_app_action.pid=?', 'tl_api_app_action.type=?'], [$app->id, $request->attributes->get('_route')]))) {
                    return false;
                }

                break;

            default:
                $allowed = StringUtil::deserialize($app->resourceActions, true);

                if (!\in_array($request->attributes->get('_route'), $allowed)) {
                    return false;
                }

                break;
        }

        return true;
    }
}
