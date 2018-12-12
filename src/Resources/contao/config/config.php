<?php

/**
 * Back end modules
 */
array_insert(
    $GLOBALS['BE_MOD'],
    1,
    [
        'api' => [
            'api_apps' => [
                'tables' => ['tl_api_app', 'tl_api_app_action'],
            ],
        ],
    ]
);

/**
 * Models
 */
$GLOBALS['TL_MODELS']['tl_api_app']        = 'HeimrichHannot\ApiBundle\Model\ApiAppModel';
$GLOBALS['TL_MODELS']['tl_api_app_action'] = 'HeimrichHannot\ApiBundle\Model\ApiAppActionModel';