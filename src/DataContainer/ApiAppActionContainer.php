<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ApiBundle\DataContainer;

use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\Date;
use Contao\System;

class ApiAppActionContainer
{
    /**
     * @Callback(table="tl_api_app_action" target="list.sorting.child_record")
     */
    public function listChildren($arrRow)
    {
        System::loadLanguageFile('tl_api_app');

        $type = $GLOBALS['TL_LANG']['tl_api_app']['reference'][$arrRow['type']];

        return '<div class="tl_content_left">'.($type ?: $arrRow['id']).' <span style="color:#b3b3b3; padding-left:3px">['.
            Date::parse(Date::getNumericDatimFormat(), trim($arrRow['dateAdded'])).']</span></div>';
    }
}
