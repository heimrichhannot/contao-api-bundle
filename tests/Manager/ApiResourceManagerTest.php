<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ApiBundle\Test\Manager;

use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\ApiBundle\ApiResource\MemberResource;
use HeimrichHannot\ApiBundle\Manager\ApiResourceManager;

class ApiResourceManagerTest extends ContaoTestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        $manager = new ApiResourceManager($this->mockContaoFramework());

        $this->assertInstanceOf('HeimrichHannot\ApiBundle\Manager\ApiResourceManager', $manager);
    }

    /**
     * Test add and overwrite resource.
     */
    public function testAddResource()
    {
        $resource = new MemberResource();
        $manager = new ApiResourceManager($this->mockContaoFramework());
        $manager->add($resource, 'member', 'huh.api.resource.member');
        $this->assertEquals($resource, $manager->get('member'));

        // test overwrite
        $mockResource = $this->createMock(MemberResource::class);
        $manager->add($mockResource, 'member', 'huh.api.resource.mock_member');
        $this->assertEquals($mockResource, $manager->get('member'));
    }

    /**
     * Test keys().
     */
    public function testGetKeys()
    {
        $resource = new MemberResource();
        $manager = new ApiResourceManager($this->mockContaoFramework());
        $manager->add($resource, 'member', 'huh.api.resource.member');
        $mockResource = $this->createMock(MemberResource::class);
        $manager->add($mockResource, 'member_mock', 'huh.api.resource.mock_member');
        $this->assertEquals(['member', 'member_mock'], $manager->keys());
    }

    /**
     * Test choices().
     */
    public function testGetChoices()
    {
        $resource = new MemberResource();
        $manager = new ApiResourceManager($this->mockContaoFramework());
        $manager->add($resource, 'member', 'huh.api.resource.member');
        $mockResource = $this->createMock(MemberResource::class);
        $manager->add($mockResource, 'member_mock', 'huh.api.resource.mock_member');
        $this->assertEquals(['member' => 'member [huh.api.resource.member]', 'member_mock' => 'member_mock [huh.api.resource.mock_member]'], $manager->choices());
    }
}
