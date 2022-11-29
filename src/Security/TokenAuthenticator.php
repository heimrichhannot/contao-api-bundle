<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ApiBundle\Security;

use HeimrichHannot\ApiBundle\Exception\ExpiredTokenException;
use HeimrichHannot\ApiBundle\Exception\InvalidJWTException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class TokenAuthenticator extends AbstractGuardAuthenticator
{
    /**
     * Called on every request. Return whatever credentials you want to
     * be passed to getUser() as $credentials.
     */
    public function getCredentials(Request $request)
    {
        if (null !== ($locale = $request->getPreferredLanguage())) {
            $this->translator->setLocale($locale);
        }

        if (!$request->headers->has('Authorization')) {
            throw new AuthenticationException($this->translator->trans('huh.api.exception.auth.missing_authorization_header'));
        }

        $headerParts = explode(' ', $request->headers->get('Authorization'));

        if (!(2 === \count($headerParts) && 'Bearer' === $headerParts[0])) {
            throw new AuthenticationException($this->translator->trans('huh.api.exception.auth.malformed_authorization_header'));
        }

        if (!$request->query->get('key')) {
            throw new AuthenticationException($this->translator->trans('huh.api.exception.auth.missing_api_key'));
        }

        return [
            'token' => $headerParts[1],
            'key' => $request->query->get('key'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        try {
            $payload = $this->jwtCoder->decode($credentials['token']);
        } catch (InvalidJWTException $e) {
            throw new AuthenticationException($this->translator->trans($e->getMessage()));
        } catch (ExpiredTokenException $e) {
            throw new AuthenticationException($this->translator->trans('huh.api.exception.auth.malformed_jwt'));
        }

        if (!isset($payload->username)) {
            throw new AuthenticationException('huh.api.exception.auth.invalid_jwt');
        }

        // if a Member object, checkCredentials() is called
        return $userProvider->loadUserByUsername(['username' => $payload->username, 'entity' => $payload->entity]);
    }

    /**
     * @var \HeimrichHannot\ApiBundle\Security\User\UserInterface
     *                                                            {@inheritdoc}
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        if (null === ($appModel = $this->container->get('huh.api.model.app')->findPublishedByKey($credentials['key']))) {
            throw new AuthenticationException($this->translator->trans('huh.api.exception.auth.invalid_api_key'));
        }

        if (false === $user->hasAppAccess($appModel)) {
            throw new AuthenticationException($this->translator->trans('huh.api.exception.auth.user_not_allowed_for_api', ['%key%' => $credentials['key']]));
        }

        $user->setApp($appModel);

        if ($appModel->type === $this->container->get('huh.api.manager.resource')::TYPE_ENTITY_RESOURCE) {
            $request = $this->container->get('request_stack')->getCurrentRequest();

            $action = $this->container->get('huh.utils.model')->callModelMethod(
                'tl_api_app_action', 'getCurrentActionConfig',
                $request->attributes->get('_route'), $appModel);

            $user->setAppAction($action);
        }

        // if user object is present here, JWT token did already match
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Request $request)
    {
        if ('api' === $request->attributes->get('_scope')) {
            return true;
        }

        return false;
    }
}
