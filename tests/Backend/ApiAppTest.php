<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ApiBundle\Test\Backend;

use Contao\DataContainer;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\ApiBundle\Backend\ApiApp;
use HeimrichHannot\ApiBundle\Model\ApiAppModel;

class ApiAppTest extends ContaoTestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        $backend = new ApiApp($this->mockContaoFramework());

        $this->assertInstanceOf('HeimrichHannot\ApiBundle\Backend\ApiApp', $backend);
    }

    /**
     * Test generateApiToken() with existing token.
     */
    public function testGenerateApiTokenWithExistingToken()
    {
        $dc = $this->createMock(DataContainer::class);

        $appModelAdapter = $this->mockAdapter(['findByPk', 'save']);
        $appModelAdapter->method('findByPk')->willReturn(null);

        $framework = $this->mockContaoFramework([ApiAppModel::class => $appModelAdapter]);

        $backend = new ApiApp($framework);
        $this->assertEquals('token', $backend->generateApiToken('token', $dc));

        $dc = $this->mockClassWithProperties(DataContainer::class, ['id' => 1]);

        $this->assertEmpty($backend->generateApiToken('', $dc));
    }

    /**
     * Test generateApiToken().
     */
    public function testGenerateApiToken()
    {
        $dc = $this->mockClassWithProperties(DataContainer::class, ['id' => 1]);

        $appModelAdapter = $this->mockAdapter(['findByPk', 'save']);
        $appModelAdapter->method('findByPk')->willReturnSelf();
        $appModelAdapter->method('save')->willReturnSelf();

        $framework = $this->mockContaoFramework([ApiAppModel::class => $appModelAdapter]);

        $backend = new ApiApp($framework);
        $token = $backend->generateApiToken('', $dc);
        $this->assertNotEmpty($token);
        $this->assertTrue((bool) preg_match('/^[a-f0-9]{32}$/', $token)); // valid md5 token?
    }
}
