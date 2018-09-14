<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\ApiBundle\Security;


use Contao\Config;
use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\System;
use HeimrichHannot\ApiBundle\Security\User\MemberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Translation\TranslatorInterface;

class MemberAuthenticator extends GuardAuthenticator
{
    /**
     * {@inheritdoc}
     */
    public function getCredentials(Request $request)
    {
        $this->translator->setLocale($request->getPreferredLanguage());

        if ('POST' !== $request->getMethod()) {
            throw new AuthenticationException($this->translator->trans('huh.api.exception.auth.post_method_only'));
        }

        return [
            'username' => $request->getUser() ?: $request->request->get('username'),
            'password' => $request->getPassword() ?: $request->request->get('password'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        return $userProvider->loadUserByUsername($credentials['username']);
    }

    /**
     * {@inheritdoc}
     * @var MemberInterface $user
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        $time          = time();
        $authenticated = password_verify($credentials['password'], $user->getPassword());
        $needsRehash   = password_needs_rehash($user->getPassword(), PASSWORD_DEFAULT);

        // Re-hash the password if the algorithm has changed
        if ($authenticated && $needsRehash) {
            $this->password = password_hash($credentials['password'], PASSWORD_DEFAULT);
        }

        // HOOK: pass credentials to callback functions
        if (!$authenticated && isset($GLOBALS['TL_HOOKS']['checkCredentials']) && is_array($GLOBALS['TL_HOOKS']['checkCredentials'])) {
            foreach ($GLOBALS['TL_HOOKS']['checkCredentials'] as $callback) {
                $authenticated = System::importStatic($callback[0], 'auth', true)->{$callback[1]}($credentials['username'], $credentials['password'], $user);

                // Authentication successfull
                if ($authenticated === true) {
                    break;
                }
            }
        }

        if (!$authenticated) {
            $user->setLoginCount($user->getLoginCount() - 1);
            $user->getModel()->save();
            throw new AuthenticationException($this->translator->trans('huh.api.exception.auth.invalid_credentials'));
        }

        $user->setLastLogin($user->getCurrentLogin());
        $user->setCurrentLogin($time);
        $user->setLoginCount(Config::get('loginCount'));
        $user->getModel()->save();

        return true;
    }

}