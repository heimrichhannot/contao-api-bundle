<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ApiBundle;

use HeimrichHannot\ApiBundle\DependencyInjection\ApiExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ContaoApiBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        return new ApiExtension();
    }
}
