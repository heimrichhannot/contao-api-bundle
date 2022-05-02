<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ApiBundle\EventListener;

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;
use HeimrichHannot\CategoriesBundle\Backend\Category;

class CategoriesListener
{
    /**
     * @Callback(table="tl_api_app", target="config.onload")
     */
    public function onLoadCallback(DataContainer $dc = null): void
    {
        PaletteManipulator::create()
            ->addField('categories', 'resourceActions')
            ->applyToPalette('resource', 'tl_api_app');

        Category::addMultipleCategoriesFieldToDca(
            'tl_api_app', 'categories',
            [
                'addPrimaryCategory' => false,
                'forcePrimaryCategory' => false,
                'mandatory' => false,
            ]
        );
    }
}
