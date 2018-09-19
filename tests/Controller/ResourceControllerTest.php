<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ApiBundle\Test\Controller;

use Contao\MemberModel;
use Contao\TestCase\ContaoTestCase;
use Contao\UserModel;
use HeimrichHannot\ApiBundle\ApiResource\MemberResource;
use HeimrichHannot\ApiBundle\Controller\ResourceController;
use HeimrichHannot\ApiBundle\Entity\User;
use HeimrichHannot\ApiBundle\Manager\ApiResourceManager;
use HeimrichHannot\ApiBundle\Model\ApiAppModel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Translation\Translator;

class ResourceControllerTest extends ContaoTestCase
{
    public function testCanBeInstantiated(): void
    {
        $controller = new ResourceController();

        $this->assertInstanceOf('HeimrichHannot\ApiBundle\Controller\ResourceController', $controller);
    }

    /**
     * Test that actions will return a json response.
     */
    public function testReturnsAResponseInTheActionMethods(): void
    {
        $container = $this->mockContainer();
        $container->set('huh.api.manager.resource', new ApiResourceManager($this->mockContaoFramework()));
        $container->set('translator', new Translator('en'));

        $controller = new ResourceController();
        $controller->setContainer($container);

        $this->assertInstanceOf(JsonResponse::class, $controller->createAction('member', new Request()));
        $this->assertInstanceOf(JsonResponse::class, $controller->updateAction(1, 'member', new Request()));
        $this->assertInstanceOf(JsonResponse::class, $controller->listAction('member', new Request()));
        $this->assertInstanceOf(JsonResponse::class, $controller->showAction(1, 'member', new Request()));
        $this->assertInstanceOf(JsonResponse::class, $controller->deleteAction(1, 'member', new Request()));
    }

    /**
     * Test that actions will check if resource does exist.
     */
    public function testActionIsAvailable(): void
    {
        $user = $this->createMock(User::class);

        $resourceManager = new ApiResourceManager($this->mockContaoFramework());
        $resourceManager->add(new MemberResource(), 'member', 'huh.api.resource.member');

        $container = $this->mockContainer();
        $authenticatedToken = $this->createMock(TokenInterface::class);
        $authenticatedToken->expects($this->any())->method('getUser')->willReturn($user);
        $tokenStorage = $this->getMockBuilder(TokenStorageInterface::class)->getMock();
        $tokenStorage->method('getToken')->willReturn($authenticatedToken);
        $container->set('security.token_storage', $tokenStorage);
        $container->set('huh.api.manager.resource', $resourceManager);
        $container->set('translator', new Translator('en'));

        $controller = new ResourceController();
        $controller->setContainer($container);

        $this->assertNotEquals('huh.api.exception.resource_not_existing', json_decode($controller->createAction('member', new Request())->getContent())->message);
        $this->assertEquals('huh.api.exception.resource_not_existing', json_decode($controller->createAction('test', new Request())->getContent())->message);
        $this->assertNotEquals('huh.api.exception.resource_not_existing', json_decode($controller->updateAction('member', 1, new Request())->getContent())->message);
        $this->assertEquals('huh.api.exception.resource_not_existing', json_decode($controller->updateAction('test', 1, new Request())->getContent())->message);
        $this->assertNotEquals('huh.api.exception.resource_not_existing', json_decode($controller->listAction('member', new Request())->getContent())->message);
        $this->assertEquals('huh.api.exception.resource_not_existing', json_decode($controller->listAction('test', new Request())->getContent())->message);
        $this->assertNotEquals('huh.api.exception.resource_not_existing', json_decode($controller->showAction('member', 1, new Request())->getContent())->message);
        $this->assertEquals('huh.api.exception.resource_not_existing', json_decode($controller->showAction('test', 1, new Request())->getContent())->message);
        $this->assertNotEquals('huh.api.exception.resource_not_existing', json_decode($controller->deleteAction('member', 1, new Request())->getContent())->message);
        $this->assertEquals('huh.api.exception.resource_not_existing', json_decode($controller->deleteAction('test', 1, new Request())->getContent())->message);
    }

    /**
     * Test that actions will check if user has access to resource.
     */
    public function testActionWithoutGroups(): void
    {
        $user = $this->createMock(User::class);

        $resourceManager = new ApiResourceManager($this->mockContaoFramework());
        $resourceManager->add(new MemberResource(), 'member', 'huh.api.resource.member');

        $container = $this->mockContainer();
        $authenticatedToken = $this->createMock(TokenInterface::class);
        $authenticatedToken->expects($this->any())->method('getUser')->willReturn($user);
        $tokenStorage = $this->getMockBuilder(TokenStorageInterface::class)->getMock();
        $tokenStorage->method('getToken')->willReturn($authenticatedToken);
        $container->set('security.token_storage', $tokenStorage);
        $container->set('huh.api.manager.resource', $resourceManager);
        $container->set('translator', new Translator('en'));

        $controller = new ResourceController();
        $controller->setContainer($container);

        $this->assertEquals('huh.api.exception.resource_action_not_allowed', json_decode($controller->createAction('member', new Request())->getContent())->message);
        $this->assertEquals('huh.api.exception.resource_action_not_allowed', json_decode($controller->updateAction('member', 1, new Request())->getContent())->message);
        $this->assertEquals('huh.api.exception.resource_action_not_allowed', json_decode($controller->listAction('member', new Request())->getContent())->message);
        $this->assertEquals('huh.api.exception.resource_action_not_allowed', json_decode($controller->showAction('member', 1, new Request())->getContent())->message);
        $this->assertEquals('huh.api.exception.resource_action_not_allowed', json_decode($controller->deleteAction('member', 1, new Request())->getContent())->message);
    }

    /**
     * Test that actions will check if user has access to resource.
     */
    public function testActionWithoutActionAccess(): void
    {
        $userModel = $this->mockClassWithProperties(UserModel::class, []);
        $userModel->method('current')->willReturnSelf();

        $user = new User($this->mockContaoFramework());
        $user->setModel($userModel);

        $appModel = $this->mockClassWithProperties(ApiAppModel::class, ['resourceActions' => ['api_resource_create', 'api_resource_show']]);
        $appModel->method('current')->willReturnSelf();
        $user->setApp($appModel);

        $memberModelAdapter = $this->mockAdapter(['getPk', 'findByPk']);
        $memberModelAdapter->method('getPk')->willReturn('id');
        $memberModelAdapter->method('findByPk')->willReturn(null);

        $framework = $this->mockContaoFramework(
            [
                MemberModel::class => $memberModelAdapter,
            ]
        );

        $container = $this->mockContainer();
        $container->set('translator', new Translator('en'));

        $memberResource = new MemberResource();
        $memberResource->setFramework($framework);
        $memberResource->setContainer($container);

        $resourceManager = new ApiResourceManager($framework);
        $resourceManager->add($memberResource, 'member', 'huh.api.resource.member');

        $authenticatedToken = $this->createMock(TokenInterface::class);
        $authenticatedToken->expects($this->any())->method('getUser')->willReturn($user);
        $tokenStorage = $this->getMockBuilder(TokenStorageInterface::class)->getMock();
        $tokenStorage->method('getToken')->willReturn($authenticatedToken);
        $container->set('security.token_storage', $tokenStorage);
        $container->set('huh.api.manager.resource', $resourceManager);

        $controller = new ResourceController();
        $controller->setContainer($container);

        $request = new Request();
        $request->attributes->set('_route', 'api_resource_create');
        $this->assertNotEquals('huh.api.exception.resource_action_not_allowed', json_decode($controller->createAction('member', $request)->getContent())->message);
        $request->attributes->set('_route', 'api_resource_update');
        $this->assertEquals('huh.api.exception.resource_action_not_allowed', json_decode($controller->updateAction('member', 1, $request)->getContent())->message);
        $request->attributes->set('_route', 'api_resource_list');
        $this->assertEquals('huh.api.exception.resource_action_not_allowed', json_decode($controller->listAction('member', $request)->getContent())->message);
        $request->attributes->set('_route', 'api_resource_show');
        $this->assertNotEquals('huh.api.exception.resource_action_not_allowed', json_decode($controller->showAction('member', 1, $request)->getContent())->message);
        $request->attributes->set('_route', 'api_resource_delete');
        $this->assertEquals('huh.api.exception.resource_action_not_allowed', json_decode($controller->deleteAction('member', 1, $request)->getContent())->message);
    }
}
