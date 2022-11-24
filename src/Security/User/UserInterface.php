<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ApiBundle\Security\User;

use Contao\Model;
use HeimrichHannot\ApiBundle\Model\ApiAppActionModel;
use HeimrichHannot\ApiBundle\Model\ApiAppModel;

interface UserInterface extends AdvancedUserInterface, \Symfony\Component\Security\Core\User\UserInterface
{
    /**
     * Set current contao member model.
     */
    public function setModel(Model $model);

    /**
     * Get the current contao member model.
     */
    public function getModel(): Model;

    /**
     * Set login count.
     *
     * @return mixed
     */
    public function setLoginCount(int $loginCount);

    /**
     * Get current login count.
     */
    public function getLoginCount(): int;

    /**
     * Set last login time.
     *
     * @return mixed
     */
    public function setLastLogin(int $lastLogin);

    /**
     * Get last login time.
     */
    public function getLastLogin(): int;

    /**
     * Set current login time.
     *
     * @return mixed
     */
    public function setCurrentLogin(int $currentLogin);

    /**
     * Get current login time.
     */
    public function getCurrentLogin(): int;

    /**
     * Find model by value.
     *
     * @param $key
     * @param $value
     *
     * @return UserInterface|null
     */
    public function findBy($key, $value): ?self;

    /**
     * Check if user has access to current app.
     */
    public function hasAppAccess(ApiAppModel $model): bool;

    /**
     * Set active app model.
     *
     * @return mixed
     */
    public function setApp(ApiAppModel $model);

    /**
     * Get active app model.
     */
    public function getApp(): ?ApiAppModel;

    /**
     * Set active app action model.
     *
     * @param ApiAppModel $model
     *
     * @return mixed
     */
    public function setAppAction(ApiAppActionModel $model);

    /**
     * Get active app action model.
     *
     * @return ApiAppModel|null
     */
    public function getAppAction(): ?ApiAppActionModel;

    /**
     * Get the model table.
     */
    public function getModelTable(): string;
}
