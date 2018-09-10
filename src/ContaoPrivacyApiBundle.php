<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\PrivacyApiBundle;

use HeimrichHannot\PrivacyApiBundle\DependencyInjection\PrivacyApiExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ContaoPrivacyApiBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        return new PrivacyApiExtension();
    }
}
