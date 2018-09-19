<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ApiBundle\Test\Model;

use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\ApiBundle\Model\ApiAppModel;
use Symfony\Component\DependencyInjection\Definition;

class ApiAppModelTest extends ContaoTestCase
{
    /**
     * @var string
     */
    protected $errorReportingDefault;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        if (!\defined('TL_ROOT')) {
            \define('TL_ROOT', $this->getFixturesDir());
        }

        $GLOBALS['TL_LANGUAGE'] = 'en';
        $GLOBALS['TL_LANG']['MSC'] = ['test' => 'bar'];

        $this->errorReportingDefault = error_reporting();
        error_reporting($this->errorReportingDefault & ~E_NOTICE);
    }

    protected function tearDown()
    {
        error_reporting($this->errorReportingDefault);
    }

    /**
     * Test findPublishedByKey without existing adapter.
     */
    public function testFindPublishedByKeyWithoutAdapter()
    {
        $model = new \stdClass();
        $model->id = 1;
        $model->key = 'testKey';

        $appModel = $this->mockClassWithProperties(ApiAppModel::class, (array) $model);

        $appModelAdapter = $this->mockAdapter(['findOneBy']);
        $appModelAdapter->method('findOneBy')->willReturn($appModel);

        $container = $this->mockContainer();

        $definition = new Definition(ApiAppModel::class, []);
        $definition->addMethodCall('setFramework', [$this->mockContaoFramework()]);
        $container->setDefinition('huh.api.model.app', $definition);

        System::setContainer($container);

        $this->assertNull($container->get('huh.api.model.app')->findPublishedByKey('testKey'));
    }

    /**
     * Test findPublishedByKeyr.
     */
    public function testFindPublishedByKey()
    {
        $model = new \stdClass();
        $model->id = 1;
        $model->key = 'testKey';

        $appModel = $this->mockClassWithProperties(ApiAppModel::class, (array) $model);

        $appModelAdapter = $this->mockAdapter(['findOneBy']);
        $appModelAdapter->method('findOneBy')->willReturn($appModel);

        $framework = $this->mockContaoFramework(
            [
                ApiAppModel::class => $appModelAdapter,
            ]
        );

        $container = $this->mockContainer();

        $definition = new Definition(ApiAppModel::class, []);
        $definition->addMethodCall('setFramework', [$framework]);
        $container->setDefinition('huh.api.model.app', $definition);

        System::setContainer($container);

        $this->assertEquals($appModel, $container->get('huh.api.model.app')->findPublishedByKey('testKey'));
    }

    /**
     * @return string
     */
    protected function getFixturesDir(): string
    {
        return __DIR__.\DIRECTORY_SEPARATOR.'..'.\DIRECTORY_SEPARATOR.'Fixtures';
    }
}
