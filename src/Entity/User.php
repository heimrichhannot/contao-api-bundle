<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\ApiBundle\Entity;

use Contao\MemberModel;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\ApiBundle\Security\User\MemberInterface;

class User implements MemberInterface
{
    /**
     * @var MemberModel
     */
    private $_model;

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

        // Check wether login is allowed (front end only)
        if (false === (bool)$this->_model->login) {
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
    public function setModel(MemberModel $model)
    {
        $this->_model = $model->current();
    }

    /**
     * @inheritDoc
     */
    public function getModel(): MemberModel
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
}