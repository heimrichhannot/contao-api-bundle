<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ApiBundle\Security\User;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\System;
use HeimrichHannot\ApiBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Translation\TranslatorInterface;

class UserProvider implements ContainerAwareInterface, UserProviderInterface
{
    use ContainerAwareTrait;

    /**
     * @var string
     */
    protected $modelClass;

    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * Constructor.
     *
     * @param ContaoFrameworkInterface $framework
     */
    public function __construct(ContaoFrameworkInterface $framework, TranslatorInterface $translator)
    {
        $this->framework = $framework;
        $this->translator = $translator;
    }

    public function loadUserByEntityAndUsername(UserInterface $user, $username)
    {
        $this->framework->initialize();

        if (!$username) {
            throw new UsernameNotFoundException($this->translator->trans('huh.api.exception.auth.invalid_username'));
        }

        if (null === ($userFound = $user->findBy('username', $username))) {
            $loaded = false;

            // HOOK: pass credentials to callback functions
            if (isset($GLOBALS['TL_HOOKS']['importUser']) && \is_array($GLOBALS['TL_HOOKS']['importUser'])) {
                /** @var System $system */
                $system = $this->framework->getAdapter(System::class);

                foreach ($GLOBALS['TL_HOOKS']['importUser'] as $callback) {
                    $loaded = $system->importStatic($callback[0], 'import', true)->{$callback[1]}($username, $this->container->get('request_stack')->getCurrentRequest()->getPassword() ?: $this->container->get('request_stack')->getCurrentRequest()->request->get('password'), $user->getModelTable());

                    // Load successfull
                    if (true === $loaded) {
                        break;
                    }
                }
            }

            // Return if the user still cannot be loaded
            if (true === $loaded && null === ($userFound = $user->findBy('username', $username))) {
                throw new UsernameNotFoundException($this->translator->trans('huh.api.exception.auth.user_not_found', ['%username%' => $username]));
            }

            throw new UsernameNotFoundException($this->translator->trans('huh.api.exception.auth.user_not_existing', ['%username%' => $username]));
        }

        return $userFound;
    }

    /**
     * @var array
     *            {@inheritdoc}
     */
    public function loadUserByUsername($attributes)
    {
        $this->framework->initialize();

        if (!isset($attributes['entity']) || empty($attributes['entity'])) {
            throw new AuthenticationException($this->translator->trans('huh.api.exception.auth.missing_entity', ['%entity%' => $attributes['entity']]));
        }

        $class = $this->container->getParameter($attributes['entity']);

        if (!class_exists($class)) {
            throw new AuthenticationException($this->translator->trans('huh.api.exception.auth.missing_entity_class', ['%entity%' => $attributes['entity']]));
        }

        /** @var UserInterface $user */
        $user = $this->framework->createInstance($class, [$this->framework]);

        return $this->loadUserByEntityAndUsername($user, $attributes['username']);
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(\Symfony\Component\Security\Core\User\UserInterface $user)
    {
        throw new UnsupportedUserException($this->translator->trans('huh.api.exception.auth.refresh_not_possible'));
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return is_subclass_of($class, User::class);
    }
}
