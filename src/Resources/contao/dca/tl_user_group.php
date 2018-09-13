<?php

/**
 * Extend default palette
 */
$GLOBALS['TL_DCA']['tl_user_group']['palettes']['default'] = str_replace('fop;', 'fop;{api_legend},apis,apip;', $GLOBALS['TL_DCA']['tl_user_group']['palettes']['default']);


/**
 * Add fields to tl_user_group
 */
$GLOBALS['TL_DCA']['tl_user_group']['fields']['apis'] = [
    'label'      => &$GLOBALS['TL_LANG']['tl_user_group']['apis'],
    'exclude'    => true,
    'inputType'  => 'checkbox',
    'foreignKey' => 'tl_api_app.title',
    'eval'       => ['multiple' => true],
    'sql'        => "blob NULL",
];

$GLOBALS['TL_DCA']['tl_user_group']['fields']['apip'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_user_group']['apip'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'options'   => ['create', 'delete'],
    'reference' => &$GLOBALS['TL_LANG']['MSC'],
    'eval'      => ['multiple' => true],
    'sql'       => "blob NULL",
];