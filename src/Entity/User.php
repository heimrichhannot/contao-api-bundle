<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ApiBundle\Entity;

use Contao\Config;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Date;
use Contao\Model;
use Contao\StringUtil;
use Contao\UserModel;
use HeimrichHannot\ApiBundle\Model\ApiAppModel;
use HeimrichHannot\ApiBundle\Security\User\UserInterface;

class User implements UserInterface
{
    /**
     * @var UserModel
     */
    protected $_model;

    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    /**
     * @var ApiAppModel
     */
    protected $_apiAppModel;

    /**
     * Table name.
     *
     * @var string
     */
    protected static $table = 'tl_user';

    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        return StringUtil::deserialize($this->_model->groups, true);
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword()
    {
        return $this->_model->password;
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return $this->_model->username;
    }

    /**
     * {@inheritdoc}
     */
    public function isAccountNonExpired()
    {
        $time = time();

        /** @var Date $dateAdapter */
        $dateAdapter = $this->framework->getAdapter(Date::class);

        // Check whether account is not active yet or anymore
        if ('' != $this->_model->start || '' != $this->_model->stop) {
            $time = $dateAdapter->floorToMinute($time);

            if ('' != $this->_model->start && $this->_model->start > $time) {
                return false;
            }

            if ('' != $this->_model->stop && $this->_model->stop <= ($time + 60)) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isAccountNonLocked()
    {
        $time = time();

        /** @var Config $configAdapter */
        $configAdapter = $this->framework->getAdapter(Config::class);

        // Check whether the account is locked
        if (($this->_model->locked + $configAdapter->get('lockPeriod')) > $time) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return false === (bool) $this->_model->disable;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function setModel(Model $model)
    {
        $this->_model = $model->current();
    }

    /**
     * {@inheritdoc}
     */
    public function getModel(): Model
    {
        return $this->_model;
    }

    /**
     * {@inheritdoc}
     */
    public function setLoginCount(int $loginCount)
    {
        $this->_model->loginCount = $loginCount;
    }

    /**
     * {@inheritdoc}
     */
    public function getLoginCount(): int
    {
        return $this->_model->loginCount;
    }

    /**
     * {@inheritdoc}
     */
    public function setLastLogin(int $lastLogin)
    {
        $this->_model->lastLogin = $lastLogin;
    }

    /**
     * {@inheritdoc}
     */
    public function getLastLogin(): int
    {
        return $this->_model->lastLogin;
    }

    /**
     * {@inheritdoc}
     */
    public function setCurrentLogin(int $currentLogin)
    {
        $this->_model->currentLogin = $currentLogin;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentLogin(): int
    {
        return $this->_model->currentLogin;
    }

    /**
     * {@inheritdoc}
     */
    public function findBy($key, $value): ?UserInterface
    {
        $class = Model::getClassFromTable(static::$table);

        if (!class_exists($class)) {
            return null;
        }

        /** @var UserModel $model */
        $model = $this->framework->getAdapter($class);

        $this->_model = $model->findBy($key, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasAppAccess(ApiAppModel $model): bool
    {
        // allow access to administrators
        if (true === (bool) $this->_model->admin) {
            return true;
        }

        if (empty($this->getRoles())) {
            return false;
        }

        $groups = StringUtil::deserialize($model->groups, true);

        if (empty($groups)) {
            return false;
        }

        if (empty(array_intersect($groups, $this->getRoles()))) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function setApp(ApiAppModel $model)
    {
        $this->_apiAppModel = $model->current();
    }

    /**
     * {@inheritdoc}
     */
    public function getApp(): ?ApiAppModel
    {
        return $this->_apiAppModel;
    }
}
