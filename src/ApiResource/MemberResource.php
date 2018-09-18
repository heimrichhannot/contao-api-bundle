<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ApiBundle\ApiResource;

use Contao\CoreBundle\Framework\FrameworkAwareInterface;
use Contao\CoreBundle\Framework\FrameworkAwareTrait;
use Contao\MemberModel;
use HeimrichHannot\ApiBundle\Security\User\UserInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Request;

class MemberResource implements ResourceInterface, FrameworkAwareInterface, ContainerAwareInterface
{
    use FrameworkAwareTrait;
    use ContainerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function create(Request $request, UserInterface $user): ?array
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function update($id, Request $request, UserInterface $user): ?array
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function list(Request $request, UserInterface $user): ?array
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function show($id, Request $request, UserInterface $user): ?array
    {
        $id = (int) $id;

        /** @var MemberModel $model */
        $adapter = $this->framework->createInstance(MemberModel::class);

        if (null === ($model = $adapter->findByPk($id))) {
            return [
                'message' => $this->container->get('translator')->trans('huh.api.message.resource.not_existing', ['%resource%' => 'member', '%id%' => $id]),
            ];
        }

        return [
            $model->row(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id, Request $request, UserInterface $user): ?array
    {
        $id = (int) $id;

        /** @var MemberModel $model */
        $adapter = $this->framework->createInstance(MemberModel::class);

        if (null === ($model = $adapter->findByPk($id))) {
            return [
                'message' => $this->container->get('translator')->trans('huh.api.message.resource.not_existing', ['%resource%' => 'member', '%id%' => $id]),
            ];
        }

        if ($model->delete() > 0) {
            return [
                'message' => $this->container->get('translator')->trans('huh.api.message.resource.delete_success', ['%resource%' => 'member', '%id%' => $id]),
            ];
        }

        return [
            'message' => $this->container->get('translator')->trans('huh.api.message.resource.delete_error', ['%resource%' => 'member', '%id%' => $id]),
        ];
    }
}
