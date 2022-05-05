<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ApiBundle\Security\User;

use Symfony\Component\Security\Core\User\AdvancedUserInterface as SymfonyAdvancedUserInterface;

if (class_exists(AdvancedUserInterface::class)) {
    interface AdvancedUserInterface extends SymfonyAdvancedUserInterface
    {
    }
} else {
    interface AdvancedUserInterface
    {
        public function isAccountNonExpired();

        public function isAccountNonLocked();

        public function isCredentialsNonExpired();

        public function isEnabled();
    }
}
