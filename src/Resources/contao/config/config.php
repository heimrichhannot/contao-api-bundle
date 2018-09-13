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
                'tables' => ['tl_api_app'],
            ],
        ],
    ]
);

/**
 * Models
 */
$GLOBALS['TL_MODELS']['tl_api_app'] = 'HeimrichHannot\ApiBundle\Model\ApiAppModel';