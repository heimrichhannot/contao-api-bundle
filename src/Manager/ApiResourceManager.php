<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\ApiBundle\Manager;


use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use HeimrichHannot\ApiBundle\ApiResource\ResourceInterface;

class ApiResourceManager
{
    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    /**
     * Available resources
     *
     * @var array
     */
    private $resources = [];

    /**
     * Resource service ids
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
     * Add a resource for given alias
     *
     * @param ResourceInterface $resource
     * @param string            $alias
     */
    public function add(ResourceInterface $resource, string $alias, string $id)
    {
        $this->resources[$alias] = $resource;
        $this->services[$alias]  = $id;
    }


    /**
     * Get a resource by alias
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
     * Get all resources
     *
     * @return array[ResourceInterface]
     */
    public function all(): array
    {
        return $this->resources;
    }

    /**
     * Get all resource keys
     */
    public function keys(): array
    {
        return array_keys($this->resources);
    }

    /**
     * Get all resources as formatted choice key => value
     *
     * @return array
     */
    public function choices(): array
    {
        $choices = [];

        foreach ($this->resources as $key => $resource) {
            $choices[$key] = sprintf($key.' ['.$this->services[$key].']');
        }

        return $choices;
    }
}