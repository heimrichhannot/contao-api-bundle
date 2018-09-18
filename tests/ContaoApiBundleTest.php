<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ApiBundle\Test;

use HeimrichHannot\ApiBundle\ContaoApiBundle;
use HeimrichHannot\ApiBundle\DependencyInjection\Compiler\ApiResourcePass;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ContaoApiBundleTest extends TestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        $bundle = new ContaoApiBundle();

        $this->assertInstanceOf('HeimrichHannot\ApiBundle\ContaoApiBundle', $bundle);
    }

    /**
     * Tests the getContainerExtension() method.
     */
    public function testReturnsTheContainerExtension()
    {
        $bundle = new ContaoApiBundle();

        $this->assertInstanceOf(
            'HeimrichHannot\ApiBundle\DependencyInjection\ApiExtension',
            $bundle->getContainerExtension()
        );
    }

    /**
     * Test the compiler passes.
     */
    public function testAddsAddCompilerPass(): void
    {
        $container = new ContainerBuilder();
        $security = new SecurityExtension();
        $container->registerExtension($security);

        $bundle = new SecurityBundle();
        $bundle->build($container);

        $container->getCompilerPassConfig()->setOptimizationPasses([]);
        $container->getCompilerPassConfig()->setRemovingPasses([]);

        $bundle = new ContaoApiBundle();
        $bundle->build($container);

        $classes = [];

        foreach ($container->getCompilerPassConfig()->getPasses() as $pass) {
            $reflection = new \ReflectionClass($pass);
            $classes[] = $reflection->getName();
        }

        $this->assertContains(ApiResourcePass::class, $classes);
    }
}
