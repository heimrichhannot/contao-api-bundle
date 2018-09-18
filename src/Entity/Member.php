<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ApiBundle\Entity;

use Contao\MemberModel;
use Contao\StringUtil;
use HeimrichHannot\ApiBundle\Model\ApiAppModel;

class Member extends User
{
    /**
     * @var MemberModel
     */
    protected $_model;

    /**
     * Table name.
     *
     * @var string
     */
    protected static $table = 'tl_member';

    /**
     * {@inheritdoc}
     */
    public function isAccountNonLocked()
    {
        // Check wether login is allowed (front end only)
        if (false === (bool) $this->_model->login) {
            return false;
        }

        return parent::isAccountNonLocked();
    }

    /**
     * {@inheritdoc}
     */
    public function hasAppAccess(ApiAppModel $model): bool
    {
        if (empty($this->getRoles())) {
            return false;
        }

        $groups = StringUtil::deserialize($model->mGroups, true);

        if (empty($groups)) {
            return false;
        }

        if (empty(array_intersect($groups, $this->getRoles()))) {
            return false;
        }

        return true;
    }
}
