<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ApiBundle\Test\Security\User;

use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\ApiBundle\Entity\Member;
use HeimrichHannot\ApiBundle\Security\User\UserProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Translation\Translator;

class UserProviderTest extends ContaoTestCase
{
    protected function tearDown()
    {
        unset($GLOBALS['TL_HOOKS']['importUser']);
    }

    public function testCanBeInstantiated(): void
    {
        $provider = new UserProvider($this->mockContaoFramework(), new Translator('en'));

        $this->assertInstanceOf('HeimrichHannot\ApiBundle\Security\User\UserProvider', $provider);
    }

    /**
     * Test loadUserByEntityAndUsername() with importUser HOOK.
     */
    public function testLoadUserByEntityAndUsernameWithImportUserHook()
    {
        $hookMock = $this->createMock(__CLASS__);
        $hookMock->method('importUserHook')->willReturnCallback(
            function ($username, $password, $table) {
                return 'user@test.tld' === $username;
            }
        );

        $systemAdapter = $this->mockAdapter(['importStatic']);
        $systemAdapter->method('importStatic')->willReturn($hookMock);

        $framework = $this->mockContaoFramework([System::class => $systemAdapter]);
        $provider = new UserProvider($framework, new Translator('en'));

        $member = $this->createMock(Member::class);
        $member->method('findBy')->with('username', 'user@test.tld')->willReturn(null);
        $member->method('findBy')->willReturnSelf();
        $member->method('getModelTable')->willReturn('tl_member');

        $request = new Request();
        $request->request->set('password', 'secret');

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $container = $this->mockContainer();
        $container->set('huh.api.test.hooks', $hookMock);
        $container->set('request_stack', $requestStack);
        $provider->setContainer($container);

        $GLOBALS['TL_HOOKS']['importUser'][] = ['huh.api.test.hooks', 'importUserHook'];

        System::setContainer($container);

        try {
            $provider->loadUserByEntityAndUsername($member, 'user@test.tld');
        } catch (UsernameNotFoundException $e) {
            $this->assertEquals('huh.api.exception.auth.user_not_found', $e->getMessage());
        }
    }

    /**
     * Test loadUserByEntityAndUsername() without existing user.
     */
    public function testLoadUserByEntityAndUsernameWithNoExistingUser()
    {
        $provider = new UserProvider($this->mockContaoFramework(), new Translator('en'));

        $member = $this->createMock(Member::class);
        $member->method('findBy')->willReturn(null);

        try {
            $provider->loadUserByEntityAndUsername($member, 'user@test.tld');
        } catch (UsernameNotFoundException $e) {
            $this->assertEquals('huh.api.exception.auth.user_not_existing', $e->getMessage());
        }
    }

    /**
     * Test loadUserByEntityAndUsername() without given username.
     */
    public function testLoadUserByEntityAndUsernameWithoutUsername()
    {
        $provider = new UserProvider($this->mockContaoFramework(), new Translator('en'));

        try {
            $provider->loadUserByEntityAndUsername(new Member($this->mockContaoFramework()), '');
        } catch (UsernameNotFoundException $e) {
            $this->assertEquals('huh.api.exception.auth.invalid_username', $e->getMessage());
        }
    }

    /**
     * Test loadUserByUsername() with no given entity attribute.
     */
    public function testLoadUserByUsernameWithoutEntity()
    {
        $provider = new UserProvider($this->mockContaoFramework(), new Translator('en'));

        try {
            $provider->loadUserByUsername(['entity' => '']);
        } catch (AuthenticationException $e) {
            $this->assertEquals('huh.api.exception.auth.missing_entity', $e->getMessage());
        }
    }

    /**
     * Test loadUserByUsername() with no given entity attribute.
     */
    public function testLoadUserByUsernameWithoutEntityClass()
    {
        $provider = new UserProvider($this->mockContaoFramework(), new Translator('en'));
        $container = $this->mockContainer();
        $container->setParameter('huh.api.entity.member', '');
        $provider->setContainer($container);

        try {
            $provider->loadUserByUsername(['entity' => 'huh.api.entity.member']);
        } catch (AuthenticationException $e) {
            $this->assertEquals('huh.api.exception.auth.missing_entity_class', $e->getMessage());
        }
    }

    /**
     * Test refreshUser.
     */
    public function testRefreshUser()
    {
        $provider = new UserProvider($this->mockContaoFramework(), new Translator('en'));

        try {
            $provider->refreshUser(new Member($this->mockContaoFramework()));
        } catch (UnsupportedUserException $e) {
            $this->assertEquals('huh.api.exception.auth.refresh_not_possible', $e->getMessage());
        }
    }

    /**
     * Test supportsClass().
     */
    public function testSupportsClass()
    {
        $provider = new UserProvider($this->mockContaoFramework(), new Translator('en'));
        $this->assertTrue($provider->supportsClass(Member::class));
    }

    public function importUserHook($username, $password, $table)
    {
        return false;
    }
}
