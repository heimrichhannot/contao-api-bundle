<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ApiBundle\Test\Controller;

use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\ApiBundle\Controller\LoginController;
use HeimrichHannot\ApiBundle\Entity\User;
use HeimrichHannot\ApiBundle\Security\JWTCoder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class LoginControllerTest extends ContaoTestCase
{
    public function testCanBeInstantiated(): void
    {
        $controller = new LoginController();

        $this->assertInstanceOf('HeimrichHannot\ApiBundle\Controller\LoginController', $controller);
    }

    /**
     * Test that login actions will return a json response.
     */
    public function testReturnsAResponseInTheActionMethods(): void
    {
        $user = $this->createMock(User::class);

        $container = $this->mockContainer();
        $container->set('huh.api.jwt_coder', new JWTCoder('secret'));
        $authenticatedToken = $this->createMock(TokenInterface::class);
        $authenticatedToken->expects($this->any())->method('getUser')->willReturn($user);

        $tokenStorage = $this->getMockBuilder(TokenStorageInterface::class)->getMock();
        $tokenStorage->method('getToken')->willReturn($authenticatedToken);
        $container->set('security.token_storage', $tokenStorage);

        $controller = new LoginController();
        $controller->setContainer($container);

        $this->assertInstanceOf(JsonResponse::class, $controller->loginMemberAction(new Request()));
        $this->assertInstanceOf(JsonResponse::class, $controller->loginUserAction(new Request()));
    }
}
