<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\ApiBundle\Entity;

use Contao\MemberModel;

class Member extends User
{
    /**
     * @var MemberModel
     */
    protected $_model;

    /**
     * Table name
     *
     * @var string
     */
    protected static $table = 'tl_member';

    /**
     * @inheritDoc
     */
    public function isAccountNonLocked()
    {
        // Check wether login is allowed (front end only)
        if (false === (bool)$this->_model->login) {
            return false;
        }

        return parent::isAccountNonLocked();
    }
}