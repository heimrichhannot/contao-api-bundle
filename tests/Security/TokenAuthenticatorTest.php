<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ApiBundle\Test\Security;

use Contao\TestCase\ContaoTestCase;
use Contao\UserModel;
use HeimrichHannot\ApiBundle\Entity\User;
use HeimrichHannot\ApiBundle\Model\ApiAppModel;
use HeimrichHannot\ApiBundle\Security\JWTCoder;
use HeimrichHannot\ApiBundle\Security\TokenAuthenticator;
use HeimrichHannot\ApiBundle\Security\User\UserProvider;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Translation\Translator;

class TokenAuthenticatorTest extends ContaoTestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        $authenticator = new TokenAuthenticator($this->mockContaoFramework(), new JWTCoder('secret'), new Translator('en'));

        $this->assertInstanceOf('HeimrichHannot\ApiBundle\Security\TokenAuthenticator', $authenticator);
    }

    /**
     * Test getCredentials() without authorization headers in request.
     */
    public function testGetCredentialsWithoutAuthorizationHeaders()
    {
        $encoder = new JWTCoder('secret');
        $framework = $this->mockContaoFramework();
        $authenticator = new TokenAuthenticator($framework, $encoder, new Translator('en'));

        $request = new Request();

        try {
            $authenticator->getCredentials($request);
        } catch (AuthenticationException $e) {
            $this->assertEquals('huh.api.exception.auth.missing_authorization_header', $e->getMessage());
        }
    }

    /**
     * Test getCredentials() without authorization Bearer token in request.
     */
    public function testGetCredentialsWithoutAuthorizationBearerToken()
    {
        $encoder = new JWTCoder('secret');
        $framework = $this->mockContaoFramework();
        $authenticator = new TokenAuthenticator($framework, $encoder, new Translator('en'));

        $request = new Request();
        $request->headers->set('Authorization', 'Bearer');

        try {
            $authenticator->getCredentials($request);
        } catch (AuthenticationException $e) {
            $this->assertEquals('huh.api.exception.auth.malformed_authorization_header', $e->getMessage());
        }
    }

    /**
     * Test getCredentials() without api key given.
     */
    public function testGetCredentialsWithoutApiKey()
    {
        $encoder = new JWTCoder('secret');
        $framework = $this->mockContaoFramework();
        $authenticator = new TokenAuthenticator($framework, $encoder, new Translator('en'));

        $request = new Request();
        $request->headers->set('Authorization', 'Bearer secret');

        try {
            $authenticator->getCredentials($request);
        } catch (AuthenticationException $e) {
            $this->assertEquals('huh.api.exception.auth.missing_api_key', $e->getMessage());
        }
    }

    /**
     * Test getCredentials().
     */
    public function testGetCredentials()
    {
        $encoder = new JWTCoder('secret');
        $framework = $this->mockContaoFramework();
        $authenticator = new TokenAuthenticator($framework, $encoder, new Translator('en'));

        $request = new Request();
        $request->headers->set('Authorization', 'Bearer SECRET_TOKEN');
        $request->query->set('key', 'API_KEY');

        $this->assertEquals(['token' => 'SECRET_TOKEN', 'key' => 'API_KEY'], $authenticator->getCredentials($request));
    }

    /**
     * Test getUser() without valid token.
     */
    public function testGetUserWithInvalidToken()
    {
        $encoder = new JWTCoder('secret');
        $framework = $this->mockContaoFramework();
        $translator = new Translator('en');
        $authenticator = new TokenAuthenticator($framework, $encoder, $translator);

        $credentials = ['token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJlbnRpdHkiOiJodWguYXBpLmVudGl0eS51c2VyIiwidXNlcm5hbWUiOiJ1c2VyQHRlc3QudGxkIiwiaWF0IjoxNTE2MjM5MDIyLCJleHAiOjE1MTYyMzkwMjJ9.aQXNQ2UwxLBMmjwhU40LKwFM9JJretMufHwmy3G56dc', 'key' => 'API_KEY'];

        $userProvider = new UserProvider($framework, $translator);

        try {
            $authenticator->getUser($credentials, $userProvider);
        } catch (AuthenticationException $e) {
            $this->assertEquals('huh.api.exception.auth.invalid_token', $e->getMessage());
        }
    }

    /**
     * Test getUser() without valid token.
     */
    public function testGetUserWithExpiredToken()
    {
        $encoder = new JWTCoder('secret');
        $framework = $this->mockContaoFramework();
        $translator = new Translator('en');
        $authenticator = new TokenAuthenticator($framework, $encoder, $translator);

        $token = $encoder->encode(['username' => 'user@test.tld', 'entity' => 'huh.api.entity.user'], -1);

        $credentials = ['token' => $token, 'key' => 'API_KEY'];

        $userProvider = new UserProvider($framework, $translator);

        try {
            $authenticator->getUser($credentials, $userProvider);
        } catch (AuthenticationException $e) {
            $this->assertEquals('huh.api.exception.auth.malformed_jwt', $e->getMessage());
        }
    }

    /**
     * Test getUser() without valid payload.
     */
    public function testGetUserWithInvalidPayload()
    {
        $encoder = new JWTCoder('secret');
        $framework = $this->mockContaoFramework();
        $translator = new Translator('en');
        $authenticator = new TokenAuthenticator($framework, $encoder, $translator);

        $token = $encoder->encode(['entity' => 'huh.api.entity.user']);

        $credentials = ['token' => $token, 'key' => 'API_KEY'];

        $userProvider = new UserProvider($framework, $translator);

        try {
            $authenticator->getUser($credentials, $userProvider);
        } catch (AuthenticationException $e) {
            $this->assertEquals('huh.api.exception.auth.invalid_jwt', $e->getMessage());
        }
    }

    /**
     * Test getUser().
     */
    public function testGetUser()
    {
        $userClass = new \stdClass();
        $userClass->username = 'user@test.tld';

        $userModel = $this->createMock(UserModel::class);
        $userModel->method('current')->willReturn($userClass);

        $user = new User($this->mockContaoFramework());
        $user->setModel($userModel);

        $userModelAdapter = $this->mockAdapter(['findBy']);
        $userModelAdapter->method('findBy')->willReturn($userClass);

        $encoder = new JWTCoder('secret');
        $framework = $this->mockContaoFramework(
            [
                UserModel::class => $userModelAdapter,
            ]
        );
        $translator = new Translator('en');
        $authenticator = new TokenAuthenticator($framework, $encoder, $translator);

        $token = $encoder->encode(['username' => 'user@test.tld', 'entity' => 'huh.api.entity.user']);

        $credentials = ['token' => $token, 'key' => 'API_KEY'];

        $container = $this->mockContainer();
        $container->setParameter('huh.api.entity.user', User::class);

        $userProvider = new UserProvider($framework, $translator);
        $userProvider->setContainer($container);

        $this->assertEquals($user->getUsername(), $authenticator->getUser($credentials, $userProvider)->getUsername());
    }

    /**
     * Test checkCredentials() with invalid api key.
     */
    public function testCheckCredentialsWithInvalidApiKey()
    {
        $framework = $this->mockContaoFramework();

        $user = new User($framework);
        $credentials = ['token' => 'SECRET_TOKEN', 'key' => 'API_KEY'];

        $encoder = new JWTCoder('secret');
        $translator = new Translator('en');
        $authenticator = new TokenAuthenticator($framework, $encoder, $translator);

        $appModelAdapter = $this->mockAdapter(['findPublishedByKey']);
        $appModelAdapter->method('findPublishedByKey')->willReturn(null);

        $container = $this->mockContainer();

        $definition = new Definition(ApiAppModel::class, []);
        $definition->addMethodCall('setFramework', [$this->mockContaoFramework()]);
        $container->setDefinition('huh.api.model.app', $definition);
        $container->setParameter('huh.api.entity.user', User::class);

        $authenticator->setContainer($container);

        try {
            $authenticator->checkCredentials($credentials, $user);
        } catch (AuthenticationException $e) {
            $this->assertEquals('huh.api.exception.auth.invalid_api_key', $e->getMessage());
        }
    }

    /**
     * Test checkCredentials() without app access.
     */
    public function testCheckCredentialsWithoutAppAccess()
    {
        $userClass = new \stdClass();
        $userClass->username = 'user@test.tld';
        $userClass->admin = false;
        $userClass->groups = serialize(['11']);

        $userModel = $this->createMock(UserModel::class);
        $userModel->method('current')->willReturn($userClass);

        $user = new User($this->mockContaoFramework());
        $user->setModel($userModel);

        $userModelAdapter = $this->mockAdapter(['findBy']);
        $userModelAdapter->method('findBy')->willReturn($userClass);

        $modelClass = new \stdClass();
        $modelClass->id = 1;
        $modelClass->key = 'testKey';
        $modelClass->groups = serialize(['12']);

        $appModel = $this->mockClassWithProperties(ApiAppModel::class, (array) $modelClass);

        $appModelAdapter = $this->mockAdapter(['findPublishedByKey', 'findOneBy']);
        $appModelAdapter->method('findPublishedByKey')->willReturn($appModel);
        $appModelAdapter->method('findOneBy')->willReturn($appModel);

        $framework = $this->mockContaoFramework([ApiAppModel::class => $appModelAdapter, UserModel::class => $userModelAdapter]);

        $credentials = ['token' => 'SECRET_TOKEN', 'key' => 'testKey'];

        $encoder = new JWTCoder('secret');
        $translator = new Translator('en');
        $authenticator = new TokenAuthenticator($framework, $encoder, $translator);

        $container = $this->mockContainer();

        $definition = new Definition(ApiAppModel::class, []);
        $definition->addMethodCall('setFramework', [$framework]);
        $container->setDefinition('huh.api.model.app', $definition);
        $container->setParameter('huh.api.entity.user', User::class);

        $authenticator->setContainer($container);

        try {
            $authenticator->checkCredentials($credentials, $user);
        } catch (AuthenticationException $e) {
            $this->assertEquals('huh.api.exception.auth.user_not_allowed_for_api', $e->getMessage());
        }
    }

    /**
     * Test checkCredentials().
     */
    public function testCheckCredentials()
    {
        $userClass = new \stdClass();
        $userClass->username = 'user@test.tld';
        $userClass->admin = false;
        $userClass->groups = serialize(['12', '22']);

        $userModel = $this->createMock(UserModel::class);
        $userModel->method('current')->willReturn($userClass);

        $user = new User($this->mockContaoFramework());
        $user->setModel($userModel);

        $userModelAdapter = $this->mockAdapter(['findBy']);
        $userModelAdapter->method('findBy')->willReturn($userClass);

        $modelClass = new \stdClass();
        $modelClass->id = 1;
        $modelClass->key = 'testKey';
        $modelClass->groups = serialize(['22']);

        $appModel = $this->mockClassWithProperties(ApiAppModel::class, (array) $modelClass);
        $appModel->method('current')->willReturnSelf();

        $appModelAdapter = $this->mockAdapter(['findPublishedByKey', 'findOneBy']);
        $appModelAdapter->method('findPublishedByKey')->willReturn($appModel);
        $appModelAdapter->method('findOneBy')->willReturn($appModel);

        $framework = $this->mockContaoFramework([ApiAppModel::class => $appModelAdapter, UserModel::class => $userModelAdapter]);

        $credentials = ['token' => 'SECRET_TOKEN', 'key' => 'testKey'];

        $encoder = new JWTCoder('secret');
        $translator = new Translator('en');
        $authenticator = new TokenAuthenticator($framework, $encoder, $translator);

        $container = $this->mockContainer();

        $definition = new Definition(ApiAppModel::class, []);
        $definition->addMethodCall('setFramework', [$framework]);
        $container->setDefinition('huh.api.model.app', $definition);
        $container->setParameter('huh.api.entity.user', User::class);

        $authenticator->setContainer($container);

        $this->assertTrue($authenticator->checkCredentials($credentials, $user));
        $this->assertEquals('testKey', $user->getApp()->key);
        $this->assertEquals(1, $user->getApp()->id);
    }

    /**
     * Test supports().
     */
    public function testSupports()
    {
        $encoder = new JWTCoder('secret');
        $translator = new Translator('en');
        $authenticator = new TokenAuthenticator($this->mockContaoFramework(), $encoder, $translator);

        $request = new Request();

        // without scope
        $request->attributes->set('_scope', '_frontend');
        $this->assertFalse($authenticator->supports($request));

        // with scope
        $request->attributes->set('_scope', 'api');
        $this->assertTrue($authenticator->supports($request));
    }
}
