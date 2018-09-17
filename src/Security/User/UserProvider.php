<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\ApiBundle\Security\User;

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Model;
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
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var string
     */
    protected $modelClass;

    /**
     * Constructor.
     *
     * @param ContaoFrameworkInterface $framework
     */
    public function __construct(ContaoFrameworkInterface $framework, TranslatorInterface $translator)
    {
        $this->framework  = $framework;
        $this->translator = $translator;
    }

    public function loadUserByEntityAndUsername(UserInterface $user, $username)
    {
        $this->framework->initialize();


        if (!$username) {
            throw new UsernameNotFoundException($this->translator->trans('huh.api.exception.auth.invalid_username'));
        }

        if (null === ($user = $user->findBy('username', $username))) {

            $loaded = false;

            // HOOK: pass credentials to callback functions
            if (isset($GLOBALS['TL_HOOKS']['importUser']) && \is_array($GLOBALS['TL_HOOKS']['importUser'])) {
                foreach ($GLOBALS['TL_HOOKS']['importUser'] as $callback) {
                    $loaded = Controller::importStatic($callback[0], 'import', true)->{$callback[1]}($username, System::getContainer()->get('request_stack')->getCurrentRequest()->getPassword() ?: System::getContainer()->get('request_stack')->getCurrentRequest()->request->get('password'), 'tl_member');

                    // Load successfull
                    if ($loaded === true) {
                        break;
                    }
                }
            }

            // Return if the user still cannot be loaded
            if (!$loaded || null === ($user = $user->findBy('username', $username))) {
                throw new UsernameNotFoundException($this->translator->trans('huh.api.exception.auth.user_not_found', ['%username%' => $username]));
            }

            throw new UsernameNotFoundException($this->translator->trans('huh.api.exception.auth.user_not_exists', ['%username%' => $username]));
        }

        return $user;
    }

    /**
     * @var array $attributes
     * {@inheritdoc}
     */
    public function loadUserByUsername($attributes)
    {
        $this->framework->initialize();

        if(!isset($attributes['entity']) || empty($attributes['entity'])){
            throw new AuthenticationException($this->translator->trans('huh.api.exception.auth.missing_entity_class', ['%entity%' => $attributes['entity']]));
        }

        $class = $this->container->getParameter($attributes['entity']);

        if(!class_exists($class)){
            throw new AuthenticationException($this->translator->trans('huh.api.exception.auth.missing_entity_class', ['%entity%' => $attributes['entity']]));
        }

        $user  = new $class($this->framework);

        return $this->loadUserByEntityAndUsername($user, $attributes['username']);
    }

    /**
     * @inheritDoc
     */
    public function refreshUser(\Symfony\Component\Security\Core\User\UserInterface $user)
    {
        throw new UnsupportedUserException($this->translator->trans('huh.api.exception.auth.refresh_not_possible'));
    }

    /**
     * @inheritDoc
     */
    public function supportsClass($class)
    {
        return is_subclass_of($class, User::class);
    }
}