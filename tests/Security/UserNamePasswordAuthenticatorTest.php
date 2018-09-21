<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ApiBundle\Test\Security;

use Contao\Config;
use Contao\MemberModel;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\ApiBundle\Entity\Member;
use HeimrichHannot\ApiBundle\Security\JWTCoder;
use HeimrichHannot\ApiBundle\Security\User\UserProvider;
use HeimrichHannot\ApiBundle\Security\UsernamePasswordAuthenticator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Translation\Translator;

class UserNamePasswordAuthenticatorTest extends ContaoTestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        $authenticator = new UsernamePasswordAuthenticator($this->mockContaoFramework(), new JWTCoder('secret'), new Translator('en'));

        $this->assertInstanceOf('HeimrichHannot\ApiBundle\Security\UsernamePasswordAuthenticator', $authenticator);
    }

    /**
     * Test supportsRememberMe().
     */
    public function testSupportsRememberMe()
    {
        $authenticator = new UsernamePasswordAuthenticator($this->mockContaoFramework(), new JWTCoder('secret'), new Translator('en'));
        $this->assertFalse($authenticator->supportsRememberMe());
    }

    /**
     * Test start().
     */
    public function testStart()
    {
        $authenticator = new UsernamePasswordAuthenticator($this->mockContaoFramework(), new JWTCoder('secret'), new Translator('en'));
        $response = $authenticator->start(new Request());
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        $this->assertEquals(['message' => 'huh.api.exception.auth.required'], json_decode($response->getContent(), true));
    }

    /**
     * Test onAuthenticationFailure().
     */
    public function testOnAuthenticationFailure()
    {
        $exception = new AuthenticationException('exception message test');

        $authenticator = new UsernamePasswordAuthenticator($this->mockContaoFramework(), new JWTCoder('secret'), new Translator('en'));
        $response = $authenticator->onAuthenticationFailure(new Request(), $exception);
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertEquals(['message' => 'exception message test'], json_decode($response->getContent(), true));
    }

    /**
     * Test onAuthenticationSuccess().
     */
    public function testOnAuthenticationSuccess()
    {
        $authenticator = new UsernamePasswordAuthenticator($this->mockContaoFramework(), new JWTCoder('secret'), new Translator('en'));
        $this->assertNull($authenticator->onAuthenticationSuccess(new Request(), $this->createMock(TokenInterface::class), 'test'));
    }

    /**
     * Test getCredentials() with wrong HTTP method.
     */
    public function testGetCredentialsWithWrongHTTPMethod()
    {
        $encoder = new JWTCoder('secret');
        $translator = new Translator('en');
        $authenticator = new UsernamePasswordAuthenticator($this->mockContaoFramework(), $encoder, $translator);

        $request = new Request();
        $request->setMethod(Request::METHOD_GET);

        try {
            $authenticator->getCredentials($request);
        } catch (AuthenticationException $e) {
            $this->assertEquals('huh.api.exception.auth.post_method_only', $e->getMessage());
        }
    }

    /**
     * Test getCredentials().
     */
    public function testGetCredentials()
    {
        $encoder = new JWTCoder('secret');
        $translator = new Translator('en');
        $authenticator = new UsernamePasswordAuthenticator($this->mockContaoFramework(), $encoder, $translator);

        $request = new Request();
        $request->setMethod(Request::METHOD_POST);

        $request->request->set('username', 'user@test.tld');
        $request->request->set('password', 'secret');
        $request->attributes->set('_entity', 'huh.api.entity.member');

        $this->assertEquals(
            [
                'username' => 'user@test.tld',
                'password' => 'secret',
                'entity' => 'huh.api.entity.member',
            ],
            $authenticator->getCredentials($request)
        );
    }

    /**
     * Test checkCredentials() with invalid password.
     */
    public function testCheckCredentialsWithInvalidPassword()
    {
        $credentials = [
            'username' => 'user@test.tld',
            'password' => 'secret',
            'entity' => 'huh.api.entity.member',
        ];

        $framework = $this->mockContaoFramework();
        $encoder = new JWTCoder('secret');
        $translator = new Translator('en');
        $authenticator = new UsernamePasswordAuthenticator($framework, $encoder, $translator);

        $memberObject = new \stdClass();
        $memberObject->username = 'user@test.tld';
        $memberObject->password = '123';
        $memberObject->loginCount = 3;

        $model = $this->mockClassWithProperties(MemberModel::class, (array) $memberObject);
        $model->method('current')->willReturnSelf();

        $member = new Member($framework);
        $member->setModel($model);

        try {
            $authenticator->checkCredentials($credentials, $member);
        } catch (AuthenticationException $e) {
            $this->assertEquals('huh.api.exception.auth.invalid_credentials', $e->getMessage());
        }
    }

    /**
     * Test checkCredentials().
     */
    public function testCheckCredentials()
    {
        $credentials = [
            'username' => 'user@test.tld',
            'password' => 'secretPassword',
            'entity' => 'huh.api.entity.member',
        ];

        $configAdapter = $this->mockAdapter(['get']);
        $configAdapter->method('get')->with('loginCount')->willReturn(3);

        $framework = $this->mockContaoFramework([Config::class => $configAdapter]);
        $encoder = new JWTCoder('secret');
        $translator = new Translator('en');
        $authenticator = new UsernamePasswordAuthenticator($framework, $encoder, $translator);

        $time = time();

        $memberObject = new \stdClass();
        $memberObject->username = 'user@test.tld';
        $memberObject->password = password_hash($credentials['password'], PASSWORD_DEFAULT);
        $memberObject->loginCount = 3;
        $memberObject->currentLogin = $time - 500;

        $model = $this->mockClassWithProperties(MemberModel::class, (array) $memberObject);
        $model->method('current')->willReturnSelf();

        $member = new Member($framework);
        $member->setModel($model);

        $this->assertTrue($authenticator->checkCredentials($credentials, $member));
    }

    /**
     * Test checkCredentials() with update password (password_needs_rehash).
     */
    public function testCheckCredentialsReHash()
    {
        $credentials = [
            'username' => 'user@test.tld',
            'password' => 'rasmuslerdorf',
            'entity' => 'huh.api.entity.member',
        ];

        $configAdapter = $this->mockAdapter(['get']);
        $configAdapter->method('get')->with('loginCount')->willReturn(3);

        $framework = $this->mockContaoFramework([Config::class => $configAdapter]);
        $encoder = new JWTCoder('secret');
        $translator = new Translator('en');
        $authenticator = new UsernamePasswordAuthenticator($framework, $encoder, $translator);

        $time = time();

        $memberObject = new \stdClass();
        $memberObject->username = 'user@test.tld';
        $memberObject->password = '$2y$07$BCryptRequires22Chrcte/VlQH0piJtjXl.0t1XkA8pw9dMXTpOq';
        $memberObject->loginCount = 3;
        $memberObject->currentLogin = $time - 500;

        $model = $this->mockClassWithProperties(MemberModel::class, (array) $memberObject);
        $model->method('current')->willReturnSelf();

        $member = new Member($framework);
        $member->setModel($model);

        $this->assertTrue($authenticator->checkCredentials($credentials, $member));
    }

    /**
     * Test checkCredentials() with checkCredentials HOOK.
     */
    public function testCheckCredentialsHook()
    {
        $credentials = [
            'username' => 'user@test.tld',
            'password' => 'secretPassword',
            'entity' => 'huh.api.entity.member',
        ];

        $time = time();

        $memberObject = new \stdClass();
        $memberObject->username = 'user@test.tld';
        $memberObject->password = 'wrongPassword';
        $memberObject->loginCount = 3;
        $memberObject->currentLogin = $time - 500;

        $hookMock = $this->createMock(__CLASS__);
        $hookMock->method('checkCredentialsHook')->willReturnCallback(
            function ($username, $password, $member) {
                return 'user@test.tld' === $username;
            }
        );

        $container = $this->mockContainer();
        $container->set('huh.api.test.hooks', $hookMock);

        $GLOBALS['TL_HOOKS']['checkCredentials'][] = ['huh.api.test.hooks', 'checkCredentialsHook'];

        System::setContainer($container);

        $configAdapter = $this->mockAdapter(['get']);
        $configAdapter->method('get')->with('loginCount')->willReturn(3);

        $systemAdapter = $this->mockAdapter(['importStatic']);
        $systemAdapter->method('importStatic')->willReturn($hookMock);

        $framework = $this->mockContaoFramework([Config::class => $configAdapter, System::class => $systemAdapter]);
        $encoder = new JWTCoder('secret');
        $translator = new Translator('en');
        $authenticator = new UsernamePasswordAuthenticator($framework, $encoder, $translator);

        $model = $this->mockClassWithProperties(MemberModel::class, (array) $memberObject);
        $model->method('current')->willReturnSelf();

        $member = new Member($framework);
        $member->setModel($model);

        $this->assertTrue($authenticator->checkCredentials($credentials, $member));
    }

    /**
     * Test getUser().
     */
    public function testGetUser()
    {
        $credentials = [
            'username' => 'user@test.tld',
            'password' => 'secretPassword',
            'entity' => 'huh.api.entity.member',
        ];

        $memberModel = $this->mockClassWithProperties(MemberModel::class, ['username' => 'user@test.tld']);
        $memberModel->method('current')->willReturnSelf();

        $memberAdapter = $this->createMock(Member::class);
        $memberAdapter->method('findBy')->willReturnSelf();
        $memberAdapter->method('getUserName')->willReturn('user@test.tld');

        $framework = $this->mockContaoFramework();
        $framework->method('createInstance')->willReturn($memberAdapter);

        $encoder = new JWTCoder('secret');
        $translator = new Translator('en');
        $authenticator = new UsernamePasswordAuthenticator($framework, $encoder, $translator);

        $container = $this->mockContainer();
        $container->setParameter('huh.api.entity.member', Member::class);

        $userProvider = new UserProvider($framework, $translator);
        $userProvider->setContainer($container);

        $this->assertEquals($memberModel->username, $authenticator->getUser($credentials, $userProvider)->getUsername());
    }

    /**
     * Test supports().
     */
    public function testSupports()
    {
        $encoder = new JWTCoder('secret');
        $translator = new Translator('en');
        $authenticator = new UsernamePasswordAuthenticator($this->mockContaoFramework(), $encoder, $translator);

        $request = new Request();

        // without scope
        $request->attributes->set('_scope', '_frontend');
        $this->assertFalse($authenticator->supports($request));

        // with login user scope
        $request->attributes->set('_scope', 'api_login_user');
        $this->assertTrue($authenticator->supports($request));

        // with login member scope
        $request->attributes->set('_scope', 'api_login_member');
        $this->assertTrue($authenticator->supports($request));
    }

    public function checkCredentialsHook($username, $password, $member)
    {
        return true;
    }
}
