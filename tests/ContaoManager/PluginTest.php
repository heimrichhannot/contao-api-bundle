<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ApiBundle\Test\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\DelegatingParser;
use Contao\ManagerPlugin\Config\ContainerBuilder;
use Contao\ManagerPlugin\PluginLoader;
use HeimrichHannot\ApiBundle\ContaoApiBundle;
use HeimrichHannot\ApiBundle\ContaoManager\Plugin;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_Matcher_InvokedCount as InvokedCount;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Test the plugin class
 * Class PluginTest.
 */
class PluginTest extends TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->container = new ContainerBuilder($this->mockPluginLoader($this->never()), []);
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        static::assertInstanceOf('HeimrichHannot\ApiBundle\ContaoManager\Plugin', new Plugin());
    }

    /**
     * Tests the bundle contao invocation.
     */
    public function testGetBundles()
    {
        $plugin = new Plugin();

        /** @var BundleConfig[] $bundles */
        $bundles = $plugin->getBundles(new DelegatingParser());

        static::assertCount(1, $bundles);
        static::assertInstanceOf(BundleConfig::class, $bundles[0]);
        static::assertEquals(ContaoApiBundle::class, $bundles[0]->getName());
        static::assertEquals([ContaoCoreBundle::class], $bundles[0]->getLoadAfter());
    }

    /**
     * Test extends route collection.
     */
    public function testAddsRouteCollection()
    {
        $loader = $this->createMock(LoaderInterface::class);
        $loader->expects($this->once())->method('load');

        $resolver = $this->createMock(LoaderResolverInterface::class);
        $resolver->method('resolve')->willReturn($loader);

        $plugin = new Plugin();
        $plugin->getRouteCollection($resolver, $this->createMock(KernelInterface::class));
    }

    /**
     * Test extend configuration.
     */
    public function testAddExtensionConfig()
    {
        $plugin = new Plugin();

        $extensionConfigs = $plugin->getExtensionConfig('', [[]], $this->container);

        $this->assertNotEmpty($extensionConfigs);
    }

    /**
     * Test extend configuration with security config.
     */
    public function testAddsSecurityExtensionConfig()
    {
        $plugin = new Plugin();

        $extensionConfigs = $plugin->getExtensionConfig('security', [[]], $this->container);

        $this->assertNotEmpty($extensionConfigs);
        $this->assertArrayHasKey(0, $extensionConfigs);
        $this->assertArrayHasKey('firewalls', $extensionConfigs[0]);
        $this->assertArrayHasKey('providers', $extensionConfigs[0]);

        $this->assertArrayHasKey('api_login_member', $extensionConfigs[0]['firewalls']);
        $this->assertArrayHasKey('api_login_user', $extensionConfigs[0]['firewalls']);
        $this->assertArrayHasKey('api', $extensionConfigs[0]['firewalls']);

        $this->assertArrayHasKey('huh.api.security.user_provider', $extensionConfigs[0]['providers']);
    }

    /**
     * Mocks the plugin loader.
     *
     * @param InvokedCount $expects
     * @param array        $plugins
     *
     * @return PluginLoader|\PHPUnit_Framework_MockObject_MockObject
     */
    private function mockPluginLoader(InvokedCount $expects, array $plugins = [])
    {
        $pluginLoader = $this->createMock(PluginLoader::class);

        $pluginLoader->expects($expects)->method('getInstancesOf')->with(PluginLoader::EXTENSION_PLUGINS)->willReturn($plugins);

        return $pluginLoader;
    }
}
