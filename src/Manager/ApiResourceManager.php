<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ApiBundle\Manager;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\DataContainer;
use Contao\System;
use HeimrichHannot\ApiBundle\ApiResource\ResourceInterface;

class ApiResourceManager
{
    const TYPE_RESOURCE = 'resource';
    const TYPE_ENTITY_RESOURCE = 'entity_resource';

    const RESOURCE_TYPES = [
        self::TYPE_RESOURCE,
        self::TYPE_ENTITY_RESOURCE,
    ];

    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    /**
     * Available resources.
     *
     * @var array
     */
    private $resources = [];

    /**
     * Resource service ids.
     *
     * @var array
     */
    private $services = [];

    /**
     * TokenAuthenticator constructor.
     *
     * @param ContaoFrameworkInterface $framework
     */
    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    /**
     * Add a resource for given alias.
     *
     * @param ResourceInterface $resource
     * @param string            $alias
     */
    public function add(ResourceInterface $resource, string $alias, string $id)
    {
        $this->resources[$alias] = $resource;
        $this->services[$alias] = $id;
    }

    /**
     * Get a resource by alias.
     *
     * @param $alias
     *
     * @return mixed
     */
    public function get($alias)
    {
        if (array_key_exists($alias, $this->resources)) {
            return $this->resources[$alias];
        }
    }

    /**
     * Get all resources.
     *
     * @return array[ResourceInterface]
     */
    public function all(): array
    {
        return $this->resources;
    }

    /**
     * Get all resource keys.
     */
    public function keys(): array
    {
        return array_keys($this->resources);
    }

    /**
     * Get all resources as formatted choice key => value.
     *
     * @return array
     */
    public function choices(DataContainer $dc): array
    {
        $choices = [];

        if (!$dc->activeRecord->type) {
            return [];
        }

        $allowedResources = $this->getResourcesByAppType($dc->activeRecord->type);

        foreach ($this->resources as $key => $resource) {
            if (\in_array($key, $allowedResources)) {
                $choices[$key] = sprintf($key.' ['.$this->services[$key].']');
            }
        }

        return $choices;
    }

    public function getResourcesByAppType(string $appType)
    {
        $resources = System::getContainer()->getParameter('huh.api');

        if (!isset($resources['api']['resources'])) {
            return [];
        }

        $result = [];

        foreach ($resources['api']['resources'] as $resource) {
            if ($resource['type'] === $appType) {
                $result[] = $resource['name'];
            }
        }

        return $result;
    }
}
