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
        /** @var MemberModel $adapter */
        $adapter = $this->framework->getAdapter(MemberModel::class);

        $data = $request->request->all();
        $pk = $adapter->getPk();

        if (empty($data)) {
            return [
                'message' => $this->container->get('translator')->trans('huh.api.message.resource.create_no_data_provided', ['%resource%' => 'member']),
            ];
        }

        if (isset($data[$pk]) && 0 < ($id = (int) $data[$pk]) && null !== ($model = $adapter->findByPk($id))) {
            return [
                'message' => $this->container->get('translator')->trans('huh.api.message.resource.create_entity_already_exists', ['%resource%' => 'member', '%id%' => $id]),
            ];
        }

        $adapter->setRow($data);
        $adapter->save();

        return [
            'message' => $this->container->get('translator')->trans('huh.api.message.resource.create_success', ['%resource%' => 'member', '%id%' => $model->{$pk}]),
            'item' => $adapter->row(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function update($id, Request $request, UserInterface $user): ?array
    {
        $id = (int) $id;

        /** @var MemberModel $adapter */
        $adapter = $this->framework->getAdapter(MemberModel::class);

        if (null === ($model = $adapter->findByPk($id))) {
            return [
                'message' => $this->container->get('translator')->trans('huh.api.message.resource.not_existing', ['%resource%' => 'member', '%id%' => $id]),
            ];
        }

        $data = $request->request->all();

        if (empty($data)) {
            return [
                'message' => $this->container->get('translator')->trans('huh.api.message.resource.update_no_data_provided', ['%resource%' => 'member', '%id%' => $id]),
            ];
        }

        $model->setRow($data);
        $model->save();

        return [
            'message' => $this->container->get('translator')->trans('huh.api.message.resource.update_success', ['%resource%' => 'member', '%id%' => $id]),
            'item' => $model->row(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function list(Request $request, UserInterface $user): ?array
    {
        /** @var MemberModel $adapter */
        $adapter = $this->framework->getAdapter(MemberModel::class);

        if (0 < ($limit = (int) $request->query->get('limit'))) {
            $options['limit'] = $limit;
        }

        if (0 < ($offset = (int) $request->query->get('offset'))) {
            $options['offset'] = $offset;
        }

        if (1 > ($total = $adapter->count())) {
            return [
                'message' => $this->container->get('translator')->trans('huh.api.message.resource.none_existing', ['%resource%' => 'member']),
            ];
        }

        /** @var MemberModel $model */
        $model = $adapter->findAll($options);

        return [
            'total' => $total,
            'items' => $model->fetchAll(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function show($id, Request $request, UserInterface $user): ?array
    {
        $id = (int) $id;

        /** @var MemberModel $adapter */
        $adapter = $this->framework->getAdapter(MemberModel::class);

        if (null === ($model = $adapter->findByPk($id))) {
            return [
                'message' => $this->container->get('translator')->trans('huh.api.message.resource.not_existing', ['%resource%' => 'member', '%id%' => $id]),
            ];
        }

        return ['item' => $model->row()];
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id, Request $request, UserInterface $user): ?array
    {
        $id = (int) $id;

        /** @var MemberModel $adapter */
        $adapter = $this->framework->getAdapter(MemberModel::class);

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
