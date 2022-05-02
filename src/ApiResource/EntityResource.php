<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ApiBundle\ApiResource;

use Contao\Controller;
use Contao\CoreBundle\Framework\FrameworkAwareTrait;
use Contao\Model;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\ApiBundle\Security\User\UserInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Request;

abstract class EntityResource implements ResourceInterface
{
    use ContainerAwareTrait;
    use FrameworkAwareTrait;

    protected $resourceName;
    protected $modelClass;
    protected $verboseName;

    /**
     * EntityResource constructor.
     */
    public function __construct(string $resourceName)
    {
        $this->container = System::getContainer();
        $this->framework = System::getContainer()->get('contao.framework');
        $this->resourceName = $resourceName;

        $resourceConfig = $this->container->get('huh.api.util.api_util')->getResourceConfigByName($resourceName);

        if (!\is_array($resourceConfig)) {
            return;
        }

        $this->verboseName = $resourceConfig['verboseName'];
        $this->modelClass = $resourceConfig['modelClass'];
    }

    /**
     * {@inheritdoc}
     */
    public function create(Request $request, UserInterface $user): ?array
    {
        /** @var Model $adapter */
        $adapter = $this->framework->getAdapter($this->modelClass);

        $data = $request->request->all();
        $pk = $adapter->getPk();

        if (empty($data)) {
            return [
                'message' => $this->container->get('translator')->trans('huh.api.message.resource.create_no_data_provided', ['%resource%' => $this->verboseName]),
            ];
        }

        if (isset($data[$pk]) && 0 < ($id = (int) $data[$pk]) && null !== ($model = $adapter->findByPk($id))) {
            return [
                'message' => $this->container->get('translator')->trans('huh.api.message.resource.create_entity_already_exists', ['%resource%' => $this->verboseName, '%id%' => $id]),
            ];
        }

        $adapter->setRow($data);
        $adapter->save();

        return [
            'message' => $this->container->get('translator')->trans('huh.api.message.resource.create_success', ['%resource%' => $this->verboseName, '%id%' => $model->{$pk}]),
            'item' => $adapter->row(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function update($id, Request $request, UserInterface $user): ?array
    {
        $id = (int) $id;

        /** @var Model $adapter */
        $adapter = $this->framework->getAdapter($this->modelClass);

        if (null === ($model = $adapter->findByPk($id))) {
            return [
                'message' => $this->container->get('translator')->trans('huh.api.message.resource.not_existing', ['%resource%' => $this->verboseName, '%id%' => $id]),
            ];
        }

        $data = $request->request->all();

        if (empty($data)) {
            return [
                'message' => $this->container->get('translator')->trans('huh.api.message.resource.update_no_data_provided', ['%resource%' => $this->verboseName, '%id%' => $id]),
            ];
        }

        $model->setRow($data);
        $model->save();

        return [
            'message' => $this->container->get('translator')->trans('huh.api.message.resource.update_success', ['%resource%' => $this->verboseName, '%id%' => $id]),
            'item' => $model->row(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function list(Request $request, UserInterface $user): ?array
    {
        $this->setLanguage($user);

        $options = [];

        /** @var Model $modelAdapter */
        $modelAdapter = $this->framework->getAdapter($this->modelClass);

        if (0 < ($limit = (int) $request->query->get('limit'))) {
            $options['limit'] = $limit;
        }

        if (0 < ($offset = (int) $request->query->get('offset'))) {
            $options['offset'] = $offset;
        }

        $columns = [];

        $this->hideUnpublishedInstances($user, $columns);
        $this->applyWhereSql($user, $columns);

        if (\count($columns) < 1) {
            $columns = null;
        }

        if (1 > ($total = $modelAdapter->countBy($columns))) {
            return [
                'message' => $this->container->get('translator')->trans('huh.api.message.resource.none_existing', ['%resource%' => $this->verboseName]),
            ];
        }

        /** @var Model $model */
        $models = $modelAdapter->findBy($columns, [], $options);

        // prepare data
        $output = $this->prepareInstances($models, $request, $user);

        return [
            'total' => $total,
            'items' => $output,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function show($id, Request $request, UserInterface $user): ?array
    {
        $this->setLanguage($user);

        $id = (int) $id;

        /** @var Model $adapter */
        $adapter = $this->framework->getAdapter($this->modelClass);

        if (null === ($model = $adapter->findByPk($id))) {
            return [
                'message' => $this->container->get('translator')->trans('huh.api.message.resource.not_existing', ['%resource%' => $this->verboseName, '%id%' => $id]),
            ];
        }

        // prepare data
        $output = $this->prepareInstances([$model], $request, $user);

        return ['item' => $output[0]];
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id, Request $request, UserInterface $user): ?array
    {
        $id = (int) $id;

        /** @var Model $adapter */
        $adapter = $this->framework->getAdapter($this->modelClass);

        if (null === ($model = $adapter->findByPk($id))) {
            return [
                'message' => $this->container->get('translator')->trans('huh.api.message.resource.not_existing', ['%resource%' => $this->verboseName, '%id%' => $id]),
            ];
        }

        if ($model->delete() > 0) {
            return [
                'message' => $this->container->get('translator')->trans('huh.api.message.resource.delete_success', ['%resource%' => $this->verboseName, '%id%' => $id]),
            ];
        }

        return [
            'message' => $this->container->get('translator')->trans('huh.api.message.resource.delete_error', ['%resource%' => $this->verboseName, '%id%' => $id]),
        ];
    }

    public function prepareInstances($instances, Request $request, UserInterface $user)
    {
        $output = [];

        $config = $user->getAppAction();

        $fields = $config->limitFields ? StringUtil::deserialize($config->limitedFields, true) : array_keys($instances[0]->row());
        $formattedFields = $config->limitFormattedFields ? StringUtil::deserialize($config->limitedFormattedFields, true) : array_keys($instances[0]->row());

        foreach ($instances as $instance) {
            $output[] = $this->prepareInstance($instance, [
                'fields' => $fields,
                'formattedFields' => $formattedFields,
            ]);
        }

        return $output;
    }

    public function prepareInstance(Model $instance, array $options)
    {
        $output = [];
        $dc = $this->container->get('huh.utils.dca')->getDCTable($instance::getTable(), $instance);

        $dca = $GLOBALS['TL_DCA'][$instance::getTable()];

        if (\is_array($dca['config']['onload_callback'])) {
            foreach ($dca['config']['onload_callback'] as $callback) {
                if (\is_array($callback)) {
                    if (!isset($arrOnload[implode(',', $callback)])) {
                        $arrOnload[implode(',', $callback)] = 0;
                    }

                    System::importStatic($callback[0])->{$callback[1]}($dc);
                } elseif (\is_callable($callback)) {
                    $callback($dc);
                }
            }
        }

        foreach ($instance->row() as $field => $value) {
            if (!\in_array($field, $options['fields'])) {
                continue;
            }

            $output[$field] = $value;

            if (!\in_array($field, $options['formattedFields'])) {
                continue;
            }

            $output[$field] = $this->container->get('huh.utils.form')->prepareSpecialValueForOutput(
                $field, $value, $dc
            );
        }

        return $output;
    }

    public function setLanguage(UserInterface $user)
    {
        if (!$user->getAppAction()->language) {
            return;
        }

        $GLOBALS['TL_LANGUAGE'] = $user->getAppAction()->language;
    }

    public function hideUnpublishedInstances(UserInterface $user, array &$columns)
    {
        $action = $user->getAppAction();

        if (!$action->hideUnpublishedInstances) {
            return;
        }

        $app = $user->getApp();
        $table = $this->container->get('huh.api.util.api_util')->getEntityTableByApp($app);

        if ($action->addPublishedStartAndStop) {
            $this->container->get('huh.utils.model')->addPublishedCheckToModelArrays(
                $table, $action->publishedField, $action->publishedStartField, $action->publishedStopField, $columns, [
                'invertPublishedField' => $action->invertPublishedField,
            ]);
        } else {
            $columns[] = $table.'.'.$action->publishedField.($action->invertPublishedField ? '!=' : '=').'1';
        }
    }

    public function applyWhereSql(UserInterface $user, array &$columns)
    {
        $action = $user->getAppAction();

        if (!$action->whereSql) {
            return;
        }

        $columns[] = '('.Controller::replaceInsertTags($action->whereSql, false).')';
    }
}
