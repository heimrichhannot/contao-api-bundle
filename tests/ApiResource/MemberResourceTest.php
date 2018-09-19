<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ApiBundle\Test\ApiResource;

use Contao\MemberModel;
use Contao\Model\Collection;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use Doctrine\DBAL\Connection;
use HeimrichHannot\ApiBundle\ApiResource\MemberResource;
use HeimrichHannot\ApiBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\Translator;

class MemberResourceTest extends ContaoTestCase
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
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        $resource = new MemberResource();

        $this->assertInstanceOf('HeimrichHannot\ApiBundle\ApiResource\MemberResource', $resource);
    }

    /**
     * Test create().
     */
    public function testCreate()
    {
        $member = new \stdClass();
        $member->id = 1000;
        $member->username = 'user@test.tld';

        $memberAdapter = $this->mockAdapter(['getPk', 'findByPk', 'setRow', 'save', 'row']);
        $memberAdapter->method('getPk')->willReturn('id');
        $memberAdapter->method('findByPk')->willReturn(null);
        $memberAdapter->method('save')->willReturnSelf();
        $memberAdapter->method('setRow')->willReturnSelf();
        $memberAdapter->method('row')->willReturn((array) $member);

        $framework = $this->mockContaoFramework([MemberModel::class => $memberAdapter]);
        $container = $this->mockContainer();

        $translator = new Translator('en');
        $container->set('translator', $translator);
        $container->set('database_connection', $this->createMock(Connection::class));

        $request = new Request();
        $user = new User($framework);

        $resource = new MemberResource();
        $resource->setFramework($framework);
        $resource->setContainer($container);

        System::setContainer($container);

        // no data provided
        $this->assertEquals(['message' => 'huh.api.message.resource.create_no_data_provided'], $resource->create($request, $user));
        $request->request->set('username', $member->username);

        // data provided
        $this->assertEquals(['message' => 'huh.api.message.resource.create_success', 'item' => (array) $member], $resource->create($request, $user));
    }

    /**
     * Test create() with already existing primary key.
     */
    public function testCreateWithExistingPK()
    {
        $member = new \stdClass();
        $member->id = 1000;
        $member->username = 'user@test.tld';

        $memberAdapter = $this->mockAdapter(['getPk', 'findByPk', 'setRow', 'save', 'row']);
        $memberAdapter->method('getPk')->willReturn('id');
        $memberAdapter->method('findByPk')->willReturn($member);
        $memberAdapter->method('save')->willReturnSelf();
        $memberAdapter->method('setRow')->willReturnSelf();
        $memberAdapter->method('row')->willReturn((array) $member);

        $framework = $this->mockContaoFramework([MemberModel::class => $memberAdapter]);
        $container = $this->mockContainer();

        $translator = new Translator('en');
        $container->set('translator', $translator);
        $container->set('database_connection', $this->createMock(Connection::class));

        $request = new Request();
        $user = new User($framework);

        $resource = new MemberResource();
        $resource->setFramework($framework);
        $resource->setContainer($container);

        System::setContainer($container);

        $request->request->set('username', $member->username);
        $request->request->set('id', 1000);
        $this->assertEquals(['message' => 'huh.api.message.resource.create_entity_already_exists'], $resource->create($request, $user));
    }

    /**
     * Test update() without an existing entity.
     */
    public function testUpdateWithoutExistingEntity()
    {
        $member = new \stdClass();
        $member->id = 1000;
        $member->username = 'user@test.tld';

        $memberAdapter = $this->mockAdapter(['getPk', 'findByPk', 'setRow', 'save', 'row']);
        $memberAdapter->method('getPk')->willReturn('id');
        $memberAdapter->method('findByPk')->willReturn(null);
        $memberAdapter->method('save')->willReturnSelf();
        $memberAdapter->method('setRow')->willReturnSelf();
        $memberAdapter->method('row')->willReturn((array) $member);

        $framework = $this->mockContaoFramework([MemberModel::class => $memberAdapter]);
        $container = $this->mockContainer();

        $translator = new Translator('en');
        $container->set('translator', $translator);
        $container->set('database_connection', $this->createMock(Connection::class));

        $request = new Request();
        $user = new User($framework);

        $resource = new MemberResource();
        $resource->setFramework($framework);
        $resource->setContainer($container);

        System::setContainer($container);

        // id does not exist
        $this->assertEquals(['message' => 'huh.api.message.resource.not_existing'], $resource->update(999, $request, $user));
    }

    /**
     * Test update().
     */
    public function testUpdate()
    {
        $member = new \stdClass();
        $member->id = 1000;
        $member->username = 'user@test.tld';

        $memberUpdate = new \stdClass();
        $memberUpdate->id = 1000;
        $memberUpdate->username = 'updated_user@test.tld';

        $memberModel = $this->createMock(MemberModel::class);
        $memberModel->method('row')->willReturn((array) $memberUpdate);

        $memberAdapter = $this->mockAdapter(['getPk', 'findByPk', 'setRow', 'save', 'row']);
        $memberAdapter->method('getPk')->willReturn('id');
        $memberAdapter->method('findByPk')->willReturn($memberModel);
        $memberAdapter->method('save')->willReturnSelf();
        $memberAdapter->method('setRow')->willReturnSelf();
        $memberAdapter->method('row')->willReturn((array) $memberUpdate);

        $framework = $this->mockContaoFramework([MemberModel::class => $memberAdapter]);
        $container = $this->mockContainer();

        $translator = new Translator('en');
        $container->set('translator', $translator);
        $container->set('database_connection', $this->createMock(Connection::class));

        $request = new Request();
        $user = new User($framework);

        $resource = new MemberResource();
        $resource->setFramework($framework);
        $resource->setContainer($container);

        System::setContainer($container);

        // no data provided
        $this->assertEquals(['message' => 'huh.api.message.resource.update_no_data_provided'], $resource->update(1000, $request, $user));

        $request->request->set('username', 'updated_user@test.tld');

        $this->assertEquals(['message' => 'huh.api.message.resource.update_success', 'item' => (array) $memberUpdate], $resource->update(1000, $request, $user));
    }

    /**
     * Test list().
     */
    public function testList()
    {
        $memberA = new \stdClass();
        $memberA->id = 1000;
        $memberA->username = 'userA@test.tld';

        $memberModelA = $this->createMock(MemberModel::class);
        $memberModelA->method('row')->willReturn((array) $memberA);

        $memberB = new \stdClass();
        $memberB->id = 1001;
        $memberB->username = 'userB@test.tld';

        $memberModelB = $this->createMock(MemberModel::class);
        $memberModelB->method('row')->willReturn((array) $memberB);

        $memberC = new \stdClass();
        $memberC->id = 1001;
        $memberC->username = 'userC@test.tld';

        $memberModelC = $this->createMock(MemberModel::class);
        $memberModelC->method('row')->willReturn((array) $memberC);

        $collection = new Collection([$memberModelA, $memberModelB, $memberModelC], 'tl_member');

        $memberAdapter = $this->mockAdapter(['findAll', 'count']);
        $memberAdapter->method('findAll')->willReturn($collection);
        $memberAdapter->method('count')->willReturn($collection->count());

        $framework = $this->mockContaoFramework([MemberModel::class => $memberAdapter]);
        $container = $this->mockContainer();

        $translator = new Translator('en');
        $container->set('translator', $translator);
        $container->set('database_connection', $this->createMock(Connection::class));

        $request = new Request();
        $user = new User($framework);

        $resource = new MemberResource();
        $resource->setFramework($framework);
        $resource->setContainer($container);

        System::setContainer($container);

        $request->query->set('limit', 2);
        $request->query->set('offset', 1);

        // no data provided
        $this->assertEquals(['total' => $collection->count(), 'items' => $collection->fetchAll()], $resource->list($request, $user));
    }

    /**
     * Test list().
     */
    public function testListWithNoItems()
    {
        $memberAdapter = $this->mockAdapter(['count']);
        $memberAdapter->method('count')->willReturn(0);

        $framework = $this->mockContaoFramework([MemberModel::class => $memberAdapter]);
        $container = $this->mockContainer();

        $translator = new Translator('en');
        $container->set('translator', $translator);
        $container->set('database_connection', $this->createMock(Connection::class));

        $request = new Request();
        $user = new User($framework);

        $resource = new MemberResource();
        $resource->setFramework($framework);
        $resource->setContainer($container);

        System::setContainer($container);

        // no data provided
        $this->assertEquals(['message' => 'huh.api.message.resource.none_existing'], $resource->list($request, $user));
    }

    /**
     * Test show() without existing entity.
     */
    public function testShowWithoutExistingEntity()
    {
        $memberAdapter = $this->mockAdapter(['findByPk']);
        $memberAdapter->method('findByPk')->willReturn(null);

        $framework = $this->mockContaoFramework([MemberModel::class => $memberAdapter]);
        $container = $this->mockContainer();

        $translator = new Translator('en');
        $container->set('translator', $translator);
        $container->set('database_connection', $this->createMock(Connection::class));

        $request = new Request();
        $user = new User($framework);

        $resource = new MemberResource();
        $resource->setFramework($framework);
        $resource->setContainer($container);

        System::setContainer($container);

        // invalid user id
        $this->assertEquals(['message' => 'huh.api.message.resource.not_existing'], $resource->show(999, $request, $user));
    }

    /**
     * Test show().
     */
    public function testShow()
    {
        $member = new \stdClass();
        $member->id = 1000;
        $member->username = 'user@test.tld';

        $memberModel = $this->createMock(MemberModel::class);
        $memberModel->method('row')->willReturn((array) $member);

        $memberAdapter = $this->mockAdapter(['getPk', 'findByPk', 'setRow', 'save', 'row']);
        $memberAdapter->method('getPk')->willReturn('id');
        $memberAdapter->method('findByPk')->willReturn($memberModel);
        $memberAdapter->method('save')->willReturnSelf();
        $memberAdapter->method('setRow')->willReturnSelf();
        $memberAdapter->method('row')->willReturn((array) $member);

        $framework = $this->mockContaoFramework([MemberModel::class => $memberAdapter]);
        $container = $this->mockContainer();

        $translator = new Translator('en');
        $container->set('translator', $translator);
        $container->set('database_connection', $this->createMock(Connection::class));

        $request = new Request();
        $user = new User($framework);

        $resource = new MemberResource();
        $resource->setFramework($framework);
        $resource->setContainer($container);

        System::setContainer($container);

        $this->assertEquals(['item' => (array) $member], $resource->show(1000, $request, $user));
    }

    /**
     * Test delete() without existing entity.
     */
    public function testDeleteWithoutExistingEntity()
    {
        $memberAdapter = $this->mockAdapter(['findByPk']);
        $memberAdapter->method('findByPk')->willReturn(null);

        $framework = $this->mockContaoFramework([MemberModel::class => $memberAdapter]);
        $container = $this->mockContainer();

        $translator = new Translator('en');
        $container->set('translator', $translator);
        $container->set('database_connection', $this->createMock(Connection::class));

        $request = new Request();
        $user = new User($framework);

        $resource = new MemberResource();
        $resource->setFramework($framework);
        $resource->setContainer($container);

        System::setContainer($container);

        // invalid user id
        $this->assertEquals(['message' => 'huh.api.message.resource.not_existing'], $resource->delete(999, $request, $user));
    }

    /**
     * Test delete() without any deleted item.
     */
    public function testDeleteOnNoSuccess()
    {
        $member = new \stdClass();
        $member->id = 1000;
        $member->username = 'user@test.tld';

        $memberModel = $this->createMock(MemberModel::class);
        $memberModel->method('row')->willReturn((array) $member);
        $memberModel->method('delete')->willReturn(0);

        $memberAdapter = $this->mockAdapter(['findByPk']);
        $memberAdapter->method('findByPk')->willReturn($memberModel);

        $framework = $this->mockContaoFramework([MemberModel::class => $memberAdapter]);
        $container = $this->mockContainer();

        $translator = new Translator('en');
        $container->set('translator', $translator);
        $container->set('database_connection', $this->createMock(Connection::class));

        $request = new Request();
        $user = new User($framework);

        $resource = new MemberResource();
        $resource->setFramework($framework);
        $resource->setContainer($container);

        System::setContainer($container);

        // invalid user id
        $this->assertEquals(['message' => 'huh.api.message.resource.delete_error'], $resource->delete(999, $request, $user));
    }

    /**
     * Test delete().
     */
    public function testDelete()
    {
        $member = new \stdClass();
        $member->id = 1000;
        $member->username = 'user@test.tld';

        $memberModel = $this->createMock(MemberModel::class);
        $memberModel->method('row')->willReturn((array) $member);
        $memberModel->method('delete')->willReturn(1);

        $memberAdapter = $this->mockAdapter(['findByPk']);
        $memberAdapter->method('findByPk')->willReturn($memberModel);

        $framework = $this->mockContaoFramework([MemberModel::class => $memberAdapter]);
        $container = $this->mockContainer();

        $translator = new Translator('en');
        $container->set('translator', $translator);
        $container->set('database_connection', $this->createMock(Connection::class));

        $request = new Request();
        $user = new User($framework);

        $resource = new MemberResource();
        $resource->setFramework($framework);
        $resource->setContainer($container);

        System::setContainer($container);

        // invalid user id
        $this->assertEquals(['message' => 'huh.api.message.resource.delete_success'], $resource->delete(999, $request, $user));
    }

    /**
     * @return string
     */
    protected function getFixturesDir(): string
    {
        return __DIR__.\DIRECTORY_SEPARATOR.'..'.\DIRECTORY_SEPARATOR.'Fixtures';
    }
}
