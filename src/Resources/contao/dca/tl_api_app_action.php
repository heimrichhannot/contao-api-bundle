<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

System::loadLanguageFile('tl_api_app');

$GLOBALS['TL_DCA']['tl_api_app_action'] = [
    'config' => [
        'dataContainer' => 'Table',
        'ptable' => 'tl_api_app',
        'enableVersioning' => true,
        'onsubmit_callback' => [
            ['huh.utils.dca', 'setDateAdded'],
        ],
        'oncopy_callback' => [
            ['huh.utils.dca', 'setDateAddedOnCopy'],
        ],
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'pid,start,stop,published' => 'index',
            ],
        ],
    ],
    'list' => [
        'label' => [
            'fields' => ['id'],
            'format' => '%s',
        ],
        'sorting' => [
            'mode' => 4,
            'fields' => ['type ASC'],
            'disableGrouping' => true,
            'headerFields' => ['title', 'tstamp'],
            'panelLayout' => 'filter;sort,search,limit',
            'child_record_callback' => ['huh.api.event_listener.data_container.api_app_action_listener', 'listChildren'],
        ],
        'global_operations' => [
            'all' => [
                'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();"',
            ],
        ],
        'operations' => [
            'edit' => [
                'label' => &$GLOBALS['TL_LANG']['tl_api_app_action']['edit'],
                'href' => 'act=edit',
                'icon' => 'edit.gif',
            ],
            'copy' => [
                'label' => &$GLOBALS['TL_LANG']['tl_api_app_action']['copy'],
                'href' => 'act=copy',
                'icon' => 'copy.gif',
            ],
            'delete' => [
                'label' => &$GLOBALS['TL_LANG']['tl_api_app_action']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\''.$GLOBALS['TL_LANG']['MSC']['deleteConfirm'].'\'))return false;Backend.getScrollOffset()"',
            ],
            'toggle' => [
                'label' => &$GLOBALS['TL_LANG']['tl_api_app_action']['toggle'],
                'icon' => 'visible.gif',
                'attributes' => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback' => ['tl_api_app_action', 'toggleIcon'],
            ],
            'show' => [
                'label' => &$GLOBALS['TL_LANG']['tl_api_app_action']['show'],
                'href' => 'act=show',
                'icon' => 'show.gif',
            ],
        ],
    ],
    'palettes' => [
        '__selector__' => ['type', 'limitFields', 'limitFormattedFields', 'hideUnpublishedInstances', 'addPublishedStartAndStop', 'published'],
        'default' => '{general_legend},type;{publish_legend},published;',
        'api_resource_create' => '{general_legend},type;{publish_legend},published;',
        'api_resource_update' => '{general_legend},type;{publish_legend},published;',
        'api_resource_list' => '{general_legend},type;{config_legend},limitFields,limitFormattedFields,language,hideUnpublishedInstances,whereSql;{publish_legend},published;',
        'api_resource_show' => '{general_legend},type;{publish_legend},published;',
        'api_resource_delete' => '{general_legend},type;{publish_legend},published;',
    ],
    'subpalettes' => [
        'limitFields' => 'limitedFields',
        'limitFormattedFields' => 'limitedFormattedFields',
        'hideUnpublishedInstances' => 'publishedField,invertPublishedField,addPublishedStartAndStop',
        'addPublishedStartAndStop' => 'publishedStartField,publishedStopField',
        'published' => 'start,stop',
    ],
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'pid' => [
            'foreignKey' => 'tl_api_app.title',
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'belongsTo', 'load' => 'lazy'],
        ],
        'tstamp' => [
            'label' => &$GLOBALS['TL_LANG']['tl_api_app_action']['tstamp'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'dateAdded' => [
            'label' => &$GLOBALS['TL_LANG']['MSC']['dateAdded'],
            'sorting' => true,
            'flag' => 6,
            'eval' => ['rgxp' => 'datim', 'doNotCopy' => true],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'type' => [
            'label' => &$GLOBALS['TL_LANG']['tl_api_app_action']['type'],
            'exclude' => true,
            'filter' => true,
            'inputType' => 'select',
            'options' => ['api_resource_create', 'api_resource_update', 'api_resource_list', 'api_resource_show', 'api_resource_delete'],
            'reference' => &$GLOBALS['TL_LANG']['tl_api_app']['reference'],
            'eval' => ['tl_class' => 'w50', 'mandatory' => true, 'includeBlankOption' => true, 'submitOnChange' => true],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'limitFields' => [
            'label' => &$GLOBALS['TL_LANG']['tl_api_app_action']['limitFields'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50', 'submitOnChange' => true],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'limitedFields' => [
            'label' => &$GLOBALS['TL_LANG']['tl_api_app_action']['limitedFields'],
            'inputType' => 'checkboxWizard',
            'options_callback' => function (DataContainer $dc) {
                if (null === ($app = System::getContainer()->get('huh.utils.model')->findModelInstanceByPk('tl_api_app', $dc->activeRecord->pid))) {
                    return [];
                }

                if (!$app->resource) {
                    return [];
                }

                return System::getContainer()->get('huh.api.util.api_util')->getResourceFieldOptions($app->resource);
            },
            'exclude' => true,
            'eval' => ['multiple' => true, 'includeBlankOption' => true, 'tl_class' => 'w50 clr autoheight'],
            'sql' => 'blob NULL',
        ],
        'limitFormattedFields' => [
            'label' => &$GLOBALS['TL_LANG']['tl_api_app_action']['limitFormattedFields'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'clr w50', 'submitOnChange' => true],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'limitedFormattedFields' => [
            'label' => &$GLOBALS['TL_LANG']['tl_api_app_action']['limitedFormattedFields'],
            'inputType' => 'checkboxWizard',
            'options_callback' => function (DataContainer $dc) {
                if (null === ($app = System::getContainer()->get('huh.utils.model')->findModelInstanceByPk('tl_api_app', $dc->activeRecord->pid))) {
                    return [];
                }

                if (!$app->resource) {
                    return [];
                }

                return System::getContainer()->get('huh.api.util.api_util')->getResourceFieldOptions($app->resource);
            },
            'exclude' => true,
            'eval' => ['multiple' => true, 'includeBlankOption' => true, 'tl_class' => 'w50 clr autoheight'],
            'sql' => 'blob NULL',
        ],
        'hideUnpublishedInstances' => [
            'label' => &$GLOBALS['TL_LANG']['tl_api_app_action']['hideUnpublishedInstances'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50 clr', 'submitOnChange' => true],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'publishedField' => [
            'label' => &$GLOBALS['TL_LANG']['tl_api_app_action']['publishedField'],
            'exclude' => true,
            'inputType' => 'select',
            'options_callback' => function (DataContainer $dc) {
                if (null === ($app = System::getContainer()->get('huh.utils.model')->findModelInstanceByPk('tl_api_app', $dc->activeRecord->pid))) {
                    return [];
                }

                return System::getContainer()->get('huh.utils.choice.field')->getCachedChoices(
                    [
                        'dataContainer' => System::getContainer()->get('huh.api.util.api_util')->getEntityTableByApp($app),
                        'inputTypes' => ['checkbox'],
                    ]
                );
            },
            'eval' => ['maxlength' => 32, 'tl_class' => 'w50', 'includeBlankOption' => true, 'chosen' => true, 'mandatory' => true],
            'sql' => "varchar(32) NOT NULL default ''",
        ],
        'invertPublishedField' => [
            'label' => &$GLOBALS['TL_LANG']['tl_api_app_action']['invertPublishedField'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'addPublishedStartAndStop' => [
            'label' => &$GLOBALS['TL_LANG']['tl_api_app_action']['addPublishedStartAndStop'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50', 'submitOnChange' => true],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'publishedStartField' => [
            'label' => &$GLOBALS['TL_LANG']['tl_api_app_action']['publishedStartField'],
            'exclude' => true,
            'filter' => true,
            'inputType' => 'select',
            'options_callback' => function (Contao\DataContainer $dc) {
                if (null === ($app = System::getContainer()->get('huh.utils.model')->findModelInstanceByPk('tl_api_app', $dc->activeRecord->pid))) {
                    return [];
                }

                return System::getContainer()->get('huh.utils.choice.field')->getCachedChoices(
                    [
                        'dataContainer' => System::getContainer()->get('huh.api.util.api_util')->getEntityTableByApp($app),
                    ]
                );
            },
            'eval' => ['chosen' => true, 'includeBlankOption' => true, 'tl_class' => 'w50', 'mandatory' => true],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'publishedStopField' => [
            'label' => &$GLOBALS['TL_LANG']['tl_api_app_action']['publishedStopField'],
            'exclude' => true,
            'filter' => true,
            'inputType' => 'select',
            'options_callback' => function (Contao\DataContainer $dc) {
                if (null === ($app = System::getContainer()->get('huh.utils.model')->findModelInstanceByPk('tl_api_app', $dc->activeRecord->pid))) {
                    return [];
                }

                return System::getContainer()->get('huh.utils.choice.field')->getCachedChoices(
                    [
                        'dataContainer' => System::getContainer()->get('huh.api.util.api_util')->getEntityTableByApp($app),
                    ]
                );
            },
            'eval' => ['chosen' => true, 'includeBlankOption' => true, 'tl_class' => 'w50', 'mandatory' => true],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'language' => [
            'label' => &$GLOBALS['TL_LANG']['tl_api_app_action']['language'],
            'exclude' => true,
            'filter' => true,
            'sorting' => true,
            'inputType' => 'select',
            'options' => \System::getLanguages(),
            'eval' => [
                'includeBlankOption' => true,
                'chosen' => true,
                'tl_class' => 'clr w50',
            ],
            'sql' => "varchar(16) NOT NULL default ''",
        ],
        'whereSql' => [
            'label' => &$GLOBALS['TL_LANG']['tl_api_app_action']['whereSql'],
            'exclude' => true,
            'inputType' => 'textarea',
            'eval' => ['rte' => 'ace|sql', 'tl_class' => 'clr', 'decodeEntities' => true],
            'sql' => 'text NULL',
        ],
        'published' => [
            'label' => &$GLOBALS['TL_LANG']['tl_api_app_action']['published'],
            'exclude' => true,
            'filter' => true,
            'inputType' => 'checkbox',
            'eval' => ['doNotCopy' => true, 'submitOnChange' => true],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'start' => [
            'label' => &$GLOBALS['TL_LANG']['tl_api_app_action']['start'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql' => "varchar(10) NOT NULL default ''",
        ],
        'stop' => [
            'label' => &$GLOBALS['TL_LANG']['tl_api_app_action']['stop'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql' => "varchar(10) NOT NULL default ''",
        ],
    ],
];

class tl_api_app_action extends \Contao\Backend
{
    public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
    {
        $user = \Contao\BackendUser::getInstance();

        if (strlen(\Contao\Input::get('tid'))) {
            $this->toggleVisibility(\Contao\Input::get('tid'), ('1' === \Contao\Input::get('state')), (@func_get_arg(12) ?: null));
            Controller::redirect(System::getReferer());
        }

        // Check permissions AFTER checking the tid, so hacking attempts are logged
        if (!$user->hasAccess('tl_api_app_action::published', 'alexf')) {
            return '';
        }

        $href .= '&amp;tid='.$row['id'].'&amp;state='.('1' === $row['published'] ? '' : 1);

        if ('1' !== $row['published']) {
            $icon = 'invisible.svg';
        }

        return '<a href="'.Controller::addToUrl($href).'&rt='.\RequestToken::get().'" title="'.\StringUtil::specialchars($title).'"'.$attributes.'>'.\Image::getHtml($icon, $label, 'data-state="'.('1' === $row['published'] ? 1 : 0).'"').'</a> ';
    }

    public function toggleVisibility($intId, $blnVisible, DataContainer $dc = null)
    {
        $user = \Contao\BackendUser::getInstance();
        $database = \Contao\Database::getInstance();

        // Set the ID and action
        \Contao\Input::setGet('id', $intId);
        \Contao\Input::setGet('act', 'toggle');

        if ($dc) {
            $dc->id = $intId; // see #8043
        }

        // Trigger the onload_callback
        if (is_array($GLOBALS['TL_DCA']['tl_api_app_action']['config']['onload_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_api_app_action']['config']['onload_callback'] as $callback) {
                if (is_array($callback)) {
                    System::importStatic($callback[0])->{$callback[1]}($dc);
                } elseif (is_callable($callback)) {
                    $callback($dc);
                }
            }
        }

        // Check the field access
        if (!$user->hasAccess('tl_api_app_action::published', 'alexf')) {
            throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to publish/unpublish api_app_action item ID '.$intId.'.');
        }

        // Set the current record
        if ($dc) {
            $objRow = $database->prepare('SELECT * FROM tl_api_app_action WHERE id=?')
                ->limit(1)
                ->execute($intId);

            if ($objRow->numRows) {
                $dc->activeRecord = $objRow;
            }
        }

        $objVersions = new \Versions('tl_api_app_action', $intId);
        $objVersions->initialize();

        // Trigger the save_callback
        if (is_array($GLOBALS['TL_DCA']['tl_api_app_action']['fields']['published']['save_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_api_app_action']['fields']['published']['save_callback'] as $callback) {
                if (is_array($callback)) {
                    $blnVisible = System::importStatic($callback[0])->{$callback[1]}($blnVisible, $dc);
                } elseif (is_callable($callback)) {
                    $blnVisible = $callback($blnVisible, $dc);
                }
            }
        }

        $time = time();

        // Update the database
        $database->prepare("UPDATE tl_api_app_action SET tstamp=$time, published='".($blnVisible ? '1' : "''")."' WHERE id=?")
            ->execute($intId);

        if ($dc) {
            $dc->activeRecord->tstamp = $time;
            $dc->activeRecord->published = ($blnVisible ? '1' : '');
        }

        // Trigger the onsubmit_callback
        if (is_array($GLOBALS['TL_DCA']['tl_api_app_action']['config']['onsubmit_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_api_app_action']['config']['onsubmit_callback'] as $callback) {
                if (is_array($callback)) {
                    System::importStatic($callback[0])->{$callback[1]}($dc);
                } elseif (is_callable($callback)) {
                    $callback($dc);
                }
            }
        }

        $objVersions->create();
    }
}
