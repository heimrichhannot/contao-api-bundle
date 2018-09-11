<?php

$GLOBALS['TL_DCA']['tl_privacy_api_app'] = [
    'config'      => [
        'dataContainer'     => 'Table',
        'enableVersioning'  => true,
        'onload_callback'   => [
            ['huh.privacy_api.backend.api_app', 'checkPermission'],
        ],
        'onsubmit_callback' => [
            ['huh.utils.dca', 'setDateAdded'],
        ],
        'oncopy_callback'   => [
            ['huh.utils.dca', 'setDateAddedOnCopy'],
        ],
        'sql'               => [
            'keys' => [
                'id'                   => 'primary',
                'start,stop,published' => 'index',
            ],
        ],
    ],
    'list'        => [
        'label'             => [
            'fields' => ['title'],
            'format' => '%s',
        ],
        'sorting'           => [
            'mode'        => 2,
            'fields'      => ['title DESC'],
            'panelLayout' => 'filter;sort,search,limit',
        ],
        'global_operations' => [
            'all' => [
                'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();"',
            ],
        ],
        'operations'        => [
            'edit'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_privacy_api_app']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.gif',
            ],
            'copy'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_privacy_api_app']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.gif',
            ],
            'delete' => [
                'label'      => &$GLOBALS['TL_LANG']['tl_privacy_api_app']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\''.$GLOBALS['TL_LANG']['MSC']['deleteConfirm'].'\'))return false;Backend.getScrollOffset()"',
            ],
            'toggle' => [
                'label'           => &$GLOBALS['TL_LANG']['tl_privacy_api_app']['toggle'],
                'icon'            => 'visible.gif',
                'attributes'      => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback' => ['huh.filter.backend.filter_config_element', 'toggleIcon'],
            ],
            'show'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_privacy_api_app']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif',
            ],
        ],
    ],
    'palettes'    => [
        '__selector__' => ['published'],
        'default'      => '{general_legend},title,author;{security_legend},apiKey,groups;{publish_legend},published',
    ],
    'subpalettes' => [
        'published' => 'start,stop',
    ],
    'fields'      => [
        'id'        => [
            'sql' => "int(10) unsigned NOT NULL auto_increment",
        ],
        'tstamp'    => [
            'label' => &$GLOBALS['TL_LANG']['tl_privacy_api_app']['tstamp'],
            'eval'  => ['rgxp' => 'datim'],
            'sql'   => "varchar(64) NOT NULL default ''",
        ],
        'dateAdded' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_privacy_api_app']['dateAdded'],
            'sorting'   => true,
            'flag'      => 7,
            'inputType' => 'text',
            'eval'      => ['rgxp' => 'datim', 'datepicker' => true, 'timepicker' => true, 'doNotCopy' => true, 'mandatory' => true, 'tl_class' => 'w50'],
            'sql'       => "varchar(64) NOT NULL default ''",
        ],
        'title'     => [
            'label'     => &$GLOBALS['TL_LANG']['tl_privacy_api_app']['title'],
            'flag'      => 1,
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'groups'    => [
            'label'      => &$GLOBALS['TL_LANG']['tl_privacy_api_app']['groups'],
            'exclude'    => true,
            'inputType'  => 'checkbox',
            'foreignKey' => 'tl_member_group.name',
            'eval'       => ['mandatory' => true, 'multiple' => true],
            'sql'        => "blob NULL",
            'relation'   => ['type' => 'hasMany', 'load' => 'lazy'],
        ],
        'apiKey'    => [
            'label'         => &$GLOBALS['TL_LANG']['tl_privacy_api_app']['apiKey'],
            'search'        => true,
            'inputType'     => 'text',
            'load_callback' => [['huh.privacy_api.backend.api_app', 'generateApiToken']],
            'eval'          => ['mandatory' => true, 'tl_class' => 'clr long', 'readonly' => true],
            'sql'           => "varchar(255) NOT NULL default ''",
        ],
        'published' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_privacy_api_app']['published'],
            'exclude'   => true,
            'filter'    => true,
            'inputType' => 'checkbox',
            'eval'      => ['doNotCopy' => true, 'submitOnChange' => true, 'tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'start'     => [
            'label'     => &$GLOBALS['TL_LANG']['tl_privacy_api_app']['start'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql'       => "varchar(10) NOT NULL default ''",
        ],
        'stop'      => [
            'label'     => &$GLOBALS['TL_LANG']['tl_privacy_api_app']['stop'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql'       => "varchar(10) NOT NULL default ''",
        ],
    ],
];