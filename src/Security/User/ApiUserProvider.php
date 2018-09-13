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
use Contao\FrontendUser;
use Contao\MemberModel;
use Contao\System;
use Contao\User;
use http\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class ApiUserProvider implements ContainerAwareInterface, UserProviderInterface
{
    use ContainerAwareTrait;

    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * Constructor.
     *
     * @param ContaoFrameworkInterface $framework
     */
    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        $this->framework->initialize();

        if ('' === $username) {
            throw new \InvalidArgumentException('No valid user name provided');
        }

        /** @var MemberModel $model */
        $model = $this->framework->createInstance(MemberModel::class);
        if (null === ($model = $model->findBy('username', $username))) {

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
            if (!$loaded || null === ($model = $model->findBy('username', $username))) {
                throw new UsernameNotFoundException(sprintf('Could not find user with name "%s".', $username));
            }

            throw new UsernameNotFoundException(sprintf('User with name "%s" does not exist.', $username));
        }

        $user = new \HeimrichHannot\ApiBundle\Entity\User();
        $user->setModel($model);

        return $user;
    }

    /**
     * @inheritDoc
     */
    public function refreshUser(UserInterface $user)
    {
        throw new UnsupportedUserException('Cannot refresh a Contao user.');
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return is_subclass_of($class, User::class);
    }

}