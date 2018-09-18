<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ApiBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Reference;

class ApiResourcePass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     *
     * @throws ServiceNotFoundException
     */
    public function process(ContainerBuilder $container)
    {
        $definition = $container->findDefinition('huh.api.manager.resource');

        // find all service IDs with the huh.api.resource tag
        $taggedServices = $container->findTaggedServiceIds('huh.api.resource');

        foreach ($taggedServices as $id => $tags) {
            // a service could have the same tag twice
            foreach ($tags as $attributes) {
                if (!isset($attributes['alias'])) {
                    throw new InvalidArgumentException(sprintf('Missing tag information "alias" on huh.api.resource tagged service "%s".', $id));
                }

                $definition->addMethodCall(
                    'add',
                    [
                        new Reference($id),
                        $attributes['alias'],
                        $id,
                    ]
                );
            }
        }
    }
}
