<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\ApiBundle\DependencyInjection\Compiler;

use HeimrichHannot\ApiBundle\Manager\ApiResourceManager;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ApiResourcePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        // always first check if the primary service is defined
        if (!$container->has('huh.api.manager.resource')) {
            return;
        }

        $definition = $container->findDefinition('huh.api.manager.resource');

        // find all service IDs with the huh.api.resource tag
        $taggedServices = $container->findTaggedServiceIds('huh.api.resource');

        foreach ($taggedServices as $id => $tags) {

            // a service could have the same tag twice
            foreach ($tags as $attributes) {
                $definition->addMethodCall(
                    'add',
                    [
                        new Reference($id),
                        $attributes["alias"],
                        $id
                    ]
                );
            }
        }
    }
}