<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ApiBundle\Test\DependencyInjection\Compiler;

use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\ApiBundle\ApiResource\MemberResource;
use HeimrichHannot\ApiBundle\DependencyInjection\Compiler\ApiResourcePass;
use HeimrichHannot\ApiBundle\Manager\ApiResourceManager;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class ApiResourcePassTest extends ContaoTestCase
{
    /**
     * Test without existing resource manager service.
     */
    public function testProcessWithMissingManagerService()
    {
        $container = new ContainerBuilder();

        $pass = new ApiResourcePass();
        $pass->process($container);
    }

    /**
     * Test without tagged resources.
     */
    public function testProcessWithoutTaggedResources()
    {
        $container = new ContainerBuilder();

        $definition = new Definition(ApiResourceManager::class, [$this->mockContaoFramework()]);
        $container->setDefinition('huh.api.manager.resource', $definition);

        $pass = new ApiResourcePass();
        $pass->process($container);

        /** @var ApiResourceManager $manager */
        $manager = $container->get('huh.api.manager.resource');
        $this->assertEmpty($manager->all());
    }

    /**
     * Test without tagged resources.
     */
    public function testProcessWithoutResourceAlias()
    {
        $container = new ContainerBuilder();

        $definition = new Definition(ApiResourceManager::class, [$this->mockContaoFramework()]);

        $container->setDefinition('huh.api.manager.resource', $definition);
        $container->register('huh.api.resource.member', MemberResource::class)->addTag('huh.api.resource', []);

        $pass = new ApiResourcePass();
        $pass->process($container);

        /** @var ApiResourceManager $manager */
        $manager = $container->get('huh.api.manager.resource');
        $this->assertEmpty($manager->all());
    }

    /**
     * Test without tagged resources.
     */
    public function testProcessWithResourceAlias()
    {
        $container = new ContainerBuilder();

        $definition = new Definition(ApiResourceManager::class, [$this->mockContaoFramework()]);

        $container->setDefinition('huh.api.manager.resource', $definition);
        $container->register('huh.api.resource.member', MemberResource::class)->addTag('huh.api.resource', ['alias' => 'member']);

        $pass = new ApiResourcePass();
        $pass->process($container);

        /** @var ApiResourceManager $manager */
        $manager = $container->get('huh.api.manager.resource');
        $this->assertCount(1, $manager->all());
    }
}
