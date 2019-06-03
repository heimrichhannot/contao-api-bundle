<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ApiBundle\Backend;

use Contao\BackendUser;
use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Database;
use Contao\DataContainer;
use Contao\Image;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\ApiBundle\Model\ApiAppModel;

class ApiApp
{
    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    public function editButton($row, $href, $label, $title, $icon, $attributes)
    {
        $resourceManager = System::getContainer()->get('huh.api.manager.resource');

        switch ($row['type']) {
            case $resourceManager::TYPE_ENTITY_RESOURCE:
                return '<a href="'.Controller::addToUrl($href.'&amp;id='.$row['id']).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ';
        }

        return '';
    }

    public function generateApiToken($value, DataContainer $dc)
    {
        if ('' !== $value) {
            return $value;
        }

        /** @var ApiAppModel $adapter */
        $adapter = $this->framework->getAdapter(ApiAppModel::class);

        if (null === ($model = $adapter->findByPk($dc->id))) {
            return $value;
        }

        $model->key = md5(uniqid('', true));
        $model->save();

        return $model->key;
    }

    /**
     * Check access to applications and permissions.
     *
     * @codeCoverageIgnore Too time-consuming to test checkPermission()
     */
    public function checkPermission()
    {
        $user = BackendUser::getInstance();
        $database = Database::getInstance();

        if ($user->isAdmin) {
            return;
        }
        // Set root IDs
        if (!\is_array($user->apis) || empty($user->apis)) {
            $root = [0];
        } else {
            $root = $user->apis;
        }
        $GLOBALS['TL_DCA']['tl_api_app']['list']['sorting']['root'] = $root;
        // Check permissions to add archives
        if (!$user->hasAccess('create', 'apip')) {
            $GLOBALS['TL_DCA']['tl_api_app']['config']['closed'] = true;
        }
        /** @var \Symfony\Component\HttpFoundation\Session\SessionInterface $objSession */
        $objSession = \System::getContainer()->get('session');
        // Check current action
        switch (\Input::get('act')) {
            case 'create':
            case 'select':
                // Allow
                break;

            case 'edit':
                // Dynamically add the record to the user profile
                if (!\in_array(\Input::get('id'), $root)) {
                    /** @var \Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface $sessionBag */
                    $sessionBag = $objSession->getBag('contao_backend');
                    $arrNew = $sessionBag->get('new_records');

                    if (\is_array($arrNew['tl_api_app']) && \in_array(System::getContainer()->get('huh.request')->get('id'), $arrNew['tl_api_app'])) {
                        // Add the permissions on group level
                        if ('custom' != $user->inherit) {
                            $objGroup = $database->execute('SELECT id, apis, apip FROM tl_user_group WHERE id IN('.implode(',', array_map('intval', $user->groups)).')');

                            while ($objGroup->next()) {
                                $arrModulep = StringUtil::deserialize($objGroup->apip);

                                if (\is_array($arrModulep) && \in_array('create', $arrModulep)) {
                                    $arrModules = StringUtil::deserialize($objGroup->apis, true);
                                    $arrModules[] = System::getContainer()->get('huh.request')->get('id');
                                    $database->prepare('UPDATE tl_user_group SET apis=? WHERE id=?')->execute(serialize($arrModules), $objGroup->id);
                                }
                            }
                        }
                        // Add the permissions on user level
                        if ('group' != $user->inherit) {
                            $user = $database->prepare('SELECT apis, apip FROM tl_user WHERE id=?')->limit(1)->execute($user->id);
                            $arrModulep = StringUtil::deserialize($user->apip);

                            if (\is_array($arrModulep) && \in_array('create', $arrModulep)) {
                                $arrModules = StringUtil::deserialize($user->apis, true);
                                $arrModules[] = System::getContainer()->get('huh.request')->get('id');
                                $database->prepare('UPDATE tl_user SET apis=? WHERE id=?')->execute(serialize($arrModules), $user->id);
                            }
                        }
                        // Add the new element to the user object
                        $root[] = System::getContainer()->get('huh.request')->get('id');
                        $user->apip = $root;
                    }
                }
            // no break;
            case 'copy':
            case 'delete':
            case 'show':
                if (!\in_array(System::getContainer()->get('huh.request')->get('id'), $root) || ('delete' == System::getContainer()->get('huh.request')->get('act') && !$user->hasAccess('delete', 'apip'))) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to '.System::getContainer()->get('huh.request')->get('act').' privacy API app ID '.System::getContainer()->get('huh.request')->get('id').'.');
                }

                break;

            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
                $session = $objSession->all();

                if ('deleteAll' == System::getContainer()->get('huh.request')->get('act') && !$user->hasAccess('delete', 'apip')) {
                    $session['CURRENT']['IDS'] = [];
                } else {
                    $session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $root);
                }
                $objSession->replace($session);

                break;

            default:
                if (\strlen(System::getContainer()->get('huh.request')->get('act'))) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to '.System::getContainer()->get('huh.request')->get('act').' privacy API apps.');
                }

                break;
        }
    }
    public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
    {
        $user = \Contao\BackendUser::getInstance();

        if (strlen(\Contao\Input::get('tid'))) {
            $this->toggleVisibility(\Contao\Input::get('tid'), (\Contao\Input::get('state') === '1'), (@func_get_arg(12) ?: null));
            Controller::redirect(\System::getReferer());
        }

        // Check permissions AFTER checking the tid, so hacking attempts are logged
        if (!$user->hasAccess('tl_api_app::published', 'alexf')) {
            return '';
        }

        $href .= '&amp;tid=' . $row['id'] . '&amp;state=' . ($row['published'] === '1' ? '' : 1);

        if ($row['published'] !== '1') {
            $icon = 'invisible.svg';
        }

        return '<a href="' . Controller::addToUrl($href) . '&rt=' . \RequestToken::get() . '" title="' . \StringUtil::specialchars($title) . '"' . $attributes . '>' . \Image::getHtml($icon, $label, 'data-state="' . ($row['published'] === '1' ? 1 : 0) . '"') . '</a> ';
    }

    public function toggleVisibility($intId, $blnVisible, \DataContainer $dc = null)
    {
        $user     = \Contao\BackendUser::getInstance();
        $database = \Contao\Database::getInstance();

        // Set the ID and action
        \Contao\Input::setGet('id', $intId);
        \Contao\Input::setGet('act', 'toggle');

        if ($dc) {
            $dc->id = $intId; // see #8043
        }

        // Trigger the onload_callback
        if (is_array($GLOBALS['TL_DCA']['tl_api_app']['config']['onload_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_api_app']['config']['onload_callback'] as $callback) {
                if (is_array($callback)) {
                    System::importStatic($callback[0])->{$callback[1]}($dc);
                } elseif (is_callable($callback)) {
                    $callback($dc);
                }
            }
        }

        // Check the field access
        if (!$user->hasAccess('tl_api_app::published', 'alexf')) {
            throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to publish/unpublish api_app item ID ' . $intId . '.');
        }

        // Set the current record
        if ($dc) {
            $objRow = $database->prepare("SELECT * FROM tl_api_app WHERE id=?")
                ->limit(1)
                ->execute($intId);

            if ($objRow->numRows) {
                $dc->activeRecord = $objRow;
            }
        }

        $objVersions = new \Versions('tl_api_app', $intId);
        $objVersions->initialize();

        // Trigger the save_callback
        if (is_array($GLOBALS['TL_DCA']['tl_api_app']['fields']['published']['save_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_api_app']['fields']['published']['save_callback'] as $callback) {
                if (is_array($callback)) {
                    $blnVisible = System::importStatic($callback[0])->{$callback[1]}($blnVisible, $dc);
                } elseif (is_callable($callback)) {
                    $blnVisible = $callback($blnVisible, $dc);
                }
            }
        }

        $time = time();

        // Update the database
        $database->prepare("UPDATE tl_api_app SET tstamp=$time, published='" . ($blnVisible ? '1' : "''") . "' WHERE id=?")
            ->execute($intId);

        if ($dc) {
            $dc->activeRecord->tstamp    = $time;
            $dc->activeRecord->published = ($blnVisible ? '1' : '');
        }

        // Trigger the onsubmit_callback
        if (is_array($GLOBALS['TL_DCA']['tl_api_app']['config']['onsubmit_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_api_app']['config']['onsubmit_callback'] as $callback) {
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
