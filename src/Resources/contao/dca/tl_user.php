<?php

/**
 * Extend default palette
 */
$GLOBALS['TL_DCA']['tl_user']['palettes']['extend'] = str_replace('fop;', 'fop;{privacy_api_legend},privacyApis,privacyApip;', $GLOBALS['TL_DCA']['tl_user']['palettes']['extend']);
$GLOBALS['TL_DCA']['tl_user']['palettes']['custom'] = str_replace('fop;', 'fop;{privacy_api_legend},privacyApis,privacyApip;', $GLOBALS['TL_DCA']['tl_user']['palettes']['custom']);


/**
 * Add fields to tl_user_group
 */
$GLOBALS['TL_DCA']['tl_user']['fields']['privacyApis'] = [
    'label'      => &$GLOBALS['TL_LANG']['tl_user']['privacyApis'],
    'exclude'    => true,
    'inputType'  => 'checkbox',
    'foreignKey' => 'tl_privacy_api_app.title',
    'eval'       => ['multiple' => true],
    'sql'        => "blob NULL",
];

$GLOBALS['TL_DCA']['tl_user']['fields']['privacyApip'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_user']['privacyApip'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'options'   => ['create', 'delete'],
    'reference' => &$GLOBALS['TL_LANG']['MSC'],
    'eval'      => ['multiple' => true],
    'sql'       => "blob NULL",
];