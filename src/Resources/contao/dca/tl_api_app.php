<?php

$GLOBALS['TL_DCA']['tl_api_app'] = [
    'config'      => [
        'dataContainer'     => 'Table',
        'enableVersioning'  => true,
        'onload_callback'   => [
            ['huh.api.backend.api_app', 'checkPermission'],
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
                'key'                  => 'unique',
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
                'label' => &$GLOBALS['TL_LANG']['tl_api_app']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.gif',
            ],
            'copy'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_api_app']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.gif',
            ],
            'delete' => [
                'label'      => &$GLOBALS['TL_LANG']['tl_api_app']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\''.$GLOBALS['TL_LANG']['MSC']['deleteConfirm'].'\'))return false;Backend.getScrollOffset()"',
            ],
            'toggle' => [
                'label'           => &$GLOBALS['TL_LANG']['tl_api_app']['toggle'],
                'icon'            => 'visible.gif',
                'attributes'      => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback' => ['huh.filter.backend.filter_config_element', 'toggleIcon'],
            ],
            'show'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_api_app']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif',
            ],
        ],
    ],
    'palettes'    => [
        '__selector__' => ['type', 'published'],
        'default'      => '{general_legend},title,type',
        'resource'     => '{general_legend},title,type,author;{resource_legend},resource,resourceActions,categories;{security_legend},key,groups,mGroups;{publish_legend},published',
    ],
    'subpalettes' => [
        'published' => 'start,stop',
    ],
    'fields'      => [
        'id'              => [
            'sql' => "int(10) unsigned NOT NULL auto_increment",
        ],
        'tstamp'          => [
            'label' => &$GLOBALS['TL_LANG']['tl_api_app']['tstamp'],
            'eval'  => ['rgxp' => 'datim'],
            'sql'   => "varchar(64) NOT NULL default ''",
        ],
        'dateAdded'       => [
            'label'     => &$GLOBALS['TL_LANG']['tl_api_app']['dateAdded'],
            'sorting'   => true,
            'flag'      => 7,
            'inputType' => 'text',
            'eval'      => ['rgxp' => 'datim', 'datepicker' => true, 'timepicker' => true, 'doNotCopy' => true, 'mandatory' => true, 'tl_class' => 'w50'],
            'sql'       => "varchar(64) NOT NULL default ''",
        ],
        'type'            => [
            'label'     => &$GLOBALS['TL_LANG']['tl_api_app']['type'],
            'flag'      => 1,
            'exclude'   => true,
            'filter'    => true,
            'inputType' => 'select',
            'options'   => ['resource'],
            'reference' => &$GLOBALS['TL_LANG']['tl_api_app']['reference']['type'],
            'eval'      => ['maxlength' => 32, 'tl_class' => 'w50 chosen', 'submitOnChange' => true],
            'sql'       => "varchar(32) NOT NULL default ''",
        ],
        'title'           => [
            'label'     => &$GLOBALS['TL_LANG']['tl_api_app']['title'],
            'flag'      => 1,
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'resource'        => [
            'label'            => &$GLOBALS['TL_LANG']['tl_api_app']['resource'],
            'flag'             => 1,
            'exclude'          => true,
            'filter'           => true,
            'inputType'        => 'select',
            'options_callback' => ['huh.api.manager.resource', 'choices'],
            'reference'        => &$GLOBALS['TL_LANG']['tl_api_app']['reference']['resource'],
            'eval'             => ['maxlength' => 32, 'tl_class' => 'w50 chosen', 'submitOnChange' => true],
            'sql'              => "varchar(32) NOT NULL default ''",
        ],
        'resourceActions' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_api_app']['resourceActions'],
            'flag'      => 1,
            'exclude'   => true,
            'filter'    => true,
            'inputType' => 'checkbox',
            'options'   => ['api_resource_create', 'api_resource_update', 'api_resource_list', 'api_resource_show', 'api_resource_delete'],
            'reference' => &$GLOBALS['TL_LANG']['tl_api_app']['reference']['resourceActions'],
            'sql'       => "blob NULL",
            'eval'      => ['multiple' => true, 'tl_class' => 'w50 autoheight'],
        ],
        'mGroups'         => [
            'label'      => &$GLOBALS['TL_LANG']['tl_api_app']['mGroups'],
            'exclude'    => true,
            'inputType'  => 'checkbox',
            'foreignKey' => 'tl_member_group.name',
            'eval'       => ['multiple' => true, 'tl_class' => 'w50 autoheight'],
            'sql'        => "blob NULL",
            'relation'   => ['type' => 'hasMany', 'load' => 'lazy'],
        ],
        'groups'          => [
            'label'      => &$GLOBALS['TL_LANG']['tl_api_app']['groups'],
            'exclude'    => true,
            'inputType'  => 'checkbox',
            'foreignKey' => 'tl_user_group.name',
            'eval'       => ['multiple' => true, 'tl_class' => 'w50 autoheight'],
            'sql'        => "blob NULL",
            'relation'   => ['type' => 'hasMany', 'load' => 'lazy'],
        ],
        'key'             => [
            'label'         => &$GLOBALS['TL_LANG']['tl_api_app']['key'],
            'search'        => true,
            'inputType'     => 'text',
            'load_callback' => [['huh.api.backend.api_app', 'generateApiToken']],
            'eval'          => ['tl_class' => 'clr long', 'unique' => true],
            'sql'           => "varchar(255) NOT NULL default ''",
        ],
        'published'       => [
            'label'     => &$GLOBALS['TL_LANG']['tl_api_app']['published'],
            'exclude'   => true,
            'filter'    => true,
            'inputType' => 'checkbox',
            'eval'      => ['doNotCopy' => true, 'submitOnChange' => true, 'tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'start'           => [
            'label'     => &$GLOBALS['TL_LANG']['tl_api_app']['start'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql'       => "varchar(10) NOT NULL default ''",
        ],
        'stop'            => [
            'label'     => &$GLOBALS['TL_LANG']['tl_api_app']['stop'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql'       => "varchar(10) NOT NULL default ''",
        ],
    ],
];

\HeimrichHannot\CategoriesBundle\Backend\Category::addMultipleCategoriesFieldToDca(
    'tl_api_app', 'categories',
    [
        'addPrimaryCategory'   => false,
        'forcePrimaryCategory' => false,
        'mandatory' => false
    ]
);