<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ApiBundle\Security\User;

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Model;
use Contao\System;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Translation\TranslatorInterface;

abstract class AbstractUserProvider implements ContainerAwareInterface, UserProviderInterface
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

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        $this->framework->initialize();
        if (!$username) {
            throw new UsernameNotFoundException($this->translator->trans('huh.api.exception.auth.invalid_username'));
        }

        /** @var Model $model */
        $model = $this->framework->createInstance($this->getModelClass());
        if (null === ($model = $model->findBy('username', $username))) {
            $loaded = false;

            // HOOK: pass credentials to callback functions
            if (isset($GLOBALS['TL_HOOKS']['importUser']) && \is_array($GLOBALS['TL_HOOKS']['importUser'])) {
                foreach ($GLOBALS['TL_HOOKS']['importUser'] as $callback) {
                    $loaded = Controller::importStatic($callback[0], 'import', true)->{$callback[1]}($username, System::getContainer()->get('request_stack')->getCurrentRequest()->getPassword() ?: System::getContainer()->get('request_stack')->getCurrentRequest()->request->get('password'), 'tl_member');

                    // Load successfull
                    if (true === $loaded) {
                        break;
                    }
                }
            }

            // Return if the user still cannot be loaded
            if (!$loaded || null === ($model = $model->findBy('username', $username))) {
                throw new UsernameNotFoundException($this->translator->trans('huh.api.exception.auth.user_not_found', ['%username%' => $username]));
            }

            throw new UsernameNotFoundException($this->translator->trans('huh.api.exception.auth.user_not_existing', ['%username%' => $username]));
        }

        return $this->setUserFromModel($model);
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        throw new UnsupportedUserException($this->translator->trans('huh.api.exception.auth.refresh_not_possible'));
    }

    /**
     * Get current contao model class.
     *
     * @return string
     */
    abstract protected function setModelClass(): string;

    /**
     * Set user from contao model.
     *
     * @param Model $model
     *
     * @return UserInterface
     */
    abstract protected function setUserFromModel(Model $model): UserInterface;
}
