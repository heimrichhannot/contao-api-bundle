<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\ApiBundle\Entity;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
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
     * Table name
     *
     * @var string
     */
    protected static $table = 'tl_user';

    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    /**
     * @inheritDoc
     */
    public function getRoles()
    {
        return StringUtil::deserialize($this->_model->groups, true);
    }

    /**
     * @inheritDoc
     */
    public function getPassword()
    {
        return $this->_model->password;
    }

    /**
     * @inheritDoc
     */
    public function getSalt()
    {
        // TODO: Implement getSalt() method.
    }

    /**
     * @inheritDoc
     */
    public function getUsername()
    {
        return $this->_model->username;
    }

    /**
     * @inheritDoc
     */
    public function isAccountNonExpired()
    {
        $time = time();

        // Check whether account is not active yet or anymore
        if ($this->_model->start != '' || $this->_model->stop != '') {
            $time = \Date::floorToMinute($time);

            if ($this->_model->start != '' && $this->_model->start > $time) {
                return false;
            }

            if ($this->_model->stop != '' && $this->_model->stop <= ($time + 60)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function isAccountNonLocked()
    {
        $time = time();

        // Check whether the account is locked
        if (($this->_model->locked + \Config::get('lockPeriod')) > $time) {
            return false;
        }

        // Check whether the account is disabled
        if ($this->_model->disable) {

            return false;
        }

        // Check whether account is not active yet or anymore
        if ($this->_model->start != '' || $this->_model->stop != '') {
            $time = \Date::floorToMinute($time);

            if ($this->_model->start != '' && $this->_model->start > $time) {
                return false;
            }

            if ($this->_model->stop != '' && $this->_model->stop <= ($time + 60)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function isEnabled()
    {
        return false === (bool)$this->_model->disable;
    }

    /**
     * @inheritDoc
     */
    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    /**
     * @inheritDoc
     */
    public function setModel(Model $model)
    {
        $this->_model = $model->current();
    }

    /**
     * @inheritDoc
     */
    public function getModel(): Model
    {
        return $this->_model;
    }

    /**
     * @inheritDoc
     */
    public function setLoginCount(int $loginCount)
    {
        $this->_model->loginCount = $loginCount;
    }

    /**
     * @inheritDoc
     */
    public function getLoginCount(): int
    {
        return $this->_model->loginCount;
    }

    /**
     * @inheritDoc
     */
    public function setLastLogin(int $lastLogin)
    {
        $this->_model->lastLogin = $lastLogin;
    }

    /**
     * @inheritDoc
     */
    public function getLastLogin(): int
    {
        return $this->_model->lastLogin;
    }

    /**
     * @inheritDoc
     */
    public function setCurrentLogin(int $currentLogin)
    {
        $this->_model->currentLogin = $currentLogin;
    }

    /**
     * @inheritDoc
     */
    public function getCurrentLogin(): int
    {
        return $this->_model->currentLogin;
    }

    /**
     * @inheritDoc
     */
    public function findBy($key, $value): ?UserInterface
    {
        $class = Model::getClassFromTable(static::$table);

        if (!class_exists($class)) {
            return null;
        }

        /** @var UserModel $model */
        $model = $this->framework->createInstance($class);

        $this->_model = $model->findBy($key, $value);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function hasAppAccess(ApiAppModel $model): bool
    {
        // allow access to administrators
        if (true === (bool)$this->_model->admin) {
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
     * @inheritDoc
     */
    public function setApp(ApiAppModel $model)
    {
        $this->_apiAppModel = $model;
    }

    /**
     * @inheritDoc
     */
    public function getApp(): ApiAppModel
    {
        return $this->_apiAppModel;
    }
}