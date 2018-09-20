<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ApiBundle\Test\Security;

use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\ApiBundle\Security\JWTCoder;
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
}
