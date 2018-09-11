<?php

/**
 * Back end modules
 */
$GLOBALS['BE_MOD']['privacy']['privacy_api'] = [
    'tables' => ['tl_privacy_api_app'],
];

/**
 * Models
 */
$GLOBALS['TL_MODELS']['tl_privacy_api_app'] = 'HeimrichHannot\PrivacyApiBundle\Model\PrivacyApiAppModel';