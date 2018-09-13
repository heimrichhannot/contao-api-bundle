<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\ApiBundle\Security\User;

use Contao\MemberModel;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

interface MemberInterface extends AdvancedUserInterface
{
    /**
     * Set current contao member model
     *
     * @param MemberModel $model
     *
     */
    public function setModel(MemberModel $model);

    /**
     * Get the current contao member model
     *
     * @return MemberModel
     */
    public function getModel(): MemberModel;

    /**
     * Set login count
     *
     * @param int $loginCount
     *
     * @return mixed
     */
    public function setLoginCount(int $loginCount);

    /**
     * Get current login count
     *
     * @return int
     */
    public function getLoginCount(): int;

    /**
     * Set last login time
     *
     * @param int $lastLogin
     *
     * @return mixed
     */
    public function setLastLogin(int $lastLogin);


    /**
     * Get last login time
     *
     * @return int
     */
    public function getLastLogin(): int;

    /**
     * Set current login time
     *
     * @param int $currentLogin
     *
     * @return mixed
     */
    public function setCurrentLogin(int $currentLogin);


    /**
     * Get current login time
     *
     * @return int
     */
    public function getCurrentLogin(): int;
}