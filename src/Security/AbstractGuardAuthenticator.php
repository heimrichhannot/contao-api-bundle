<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ApiBundle\Security;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Translation\TranslatorInterface;

abstract class AbstractGuardAuthenticator extends \Symfony\Component\Security\Guard\AbstractGuardAuthenticator implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var JWTCoder
     */
    protected $jwtCoder;

    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * TokenAuthenticator constructor.
     *
     * @param ContaoFrameworkInterface $framework
     * @param JWTCoder                 $jwtCoder
     */
    public function __construct(ContaoFrameworkInterface $framework, JWTCoder $jwtCoder, TranslatorInterface $translator)
    {
        $this->framework = $framework;
        $this->jwtCoder = $jwtCoder;
        $this->translator = $translator;
    }

    /**
     * Called when authentication is needed, but it's not sent.
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $data = [
            // you might translate this message
            'message' => $this->translator->trans('huh.api.exception.auth.required'),
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $data = [
            'message' => strtr($exception->getMessage(), $exception->getMessageData()),

            // or to translate this message
            // $this->translator->trans($exception->getMessageKey(), $exception->getMessageData())
        ];

        return new JsonResponse($data, Response::HTTP_FORBIDDEN);
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // on success, let the request continue
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsRememberMe()
    {
        return false;
    }
}
