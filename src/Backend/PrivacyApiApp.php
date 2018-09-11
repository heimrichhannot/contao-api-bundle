<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\PrivacyApiBundle\Backend;


use Contao\BackendUser;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Database;
use Contao\DataContainer;
use Contao\StringUtil;
use Contao\System;
use Firebase\JWT\JWT;
use HeimrichHannot\PrivacyApiBundle\Model\PrivacyApiAppModel;

class PrivacyApiApp
{
    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    public function generateApiToken($value, DataContainer $dc)
    {
        if ('' !== $value) {
            return $value;
        }

        /** @var PrivacyApiAppModel $model */
        $model = $this->framework->createInstance(PrivacyApiAppModel::class);

        if (null === ($model = $model->findByPk($dc->id))) {
            return $value;
        }

        $data = ['token' => md5(uniqid('', true)), 'app' => $dc->id];
        $jwt  = JWT::encode($data, System::getContainer()->getParameter('secret'));

        $model->apiKey = $jwt;
        $model->save();

        return $model->apiKey;
    }

    /**
     * Check access to applications and permissions
     */
    public function checkPermission()
    {
        $user     = BackendUser::getInstance();
        $database = Database::getInstance();

        if ($user->isAdmin) {
            return;
        }
        // Set root IDs
        if (!is_array($user->privacyApis) || empty($user->privacyApis)) {
            $root = [0];
        } else {
            $root = $user->privacyApis;
        }
        $GLOBALS['TL_DCA']['tl_privacy_api_app']['list']['sorting']['root'] = $root;
        // Check permissions to add archives
        if (!$user->hasAccess('create', 'privacyApip')) {
            $GLOBALS['TL_DCA']['tl_privacy_api_app']['config']['closed'] = true;
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
                if (!in_array(\Input::get('id'), $root)) {
                    /** @var \Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface $sessionBag */
                    $sessionBag = $objSession->getBag('contao_backend');
                    $arrNew     = $sessionBag->get('new_records');
                    if (is_array($arrNew['tl_privacy_api_app']) && in_array(System::getContainer()->get('huh.request')->get('id'), $arrNew['tl_privacy_api_app'])) {
                        // Add the permissions on group level
                        if ($user->inherit != 'custom') {
                            $objGroup = $database->execute("SELECT id, privacyApis, privacyApip FROM tl_user_group WHERE id IN(".implode(',', array_map('intval', $user->groups)).")");
                            while ($objGroup->next()) {
                                $arrModulep = StringUtil::deserialize($objGroup->privacyApip);
                                if (is_array($arrModulep) && in_array('create', $arrModulep)) {
                                    $arrModules   = StringUtil::deserialize($objGroup->privacyApis, true);
                                    $arrModules[] = System::getContainer()->get('huh.request')->get('id');
                                    $database->prepare("UPDATE tl_user_group SET privacyApis=? WHERE id=?")->execute(serialize($arrModules), $objGroup->id);
                                }
                            }
                        }
                        // Add the permissions on user level
                        if ($user->inherit != 'group') {
                            $user       = $database->prepare("SELECT privacyApis, privacyApip FROM tl_user WHERE id=?")->limit(1)->execute($user->id);
                            $arrModulep = StringUtil::deserialize($user->privacyApip);
                            if (is_array($arrModulep) && in_array('create', $arrModulep)) {
                                $arrModules   = StringUtil::deserialize($user->privacyApis, true);
                                $arrModules[] = System::getContainer()->get('huh.request')->get('id');
                                $database->prepare("UPDATE tl_user SET privacyApis=? WHERE id=?")->execute(serialize($arrModules), $user->id);
                            }
                        }
                        // Add the new element to the user object
                        $root[]            = System::getContainer()->get('huh.request')->get('id');
                        $user->privacyApip = $root;
                    }
                }
            // No break;
            case 'copy':
            case 'delete':
            case 'show':
                if (!in_array(System::getContainer()->get('huh.request')->get('id'), $root) || (System::getContainer()->get('huh.request')->get('act') == 'delete' && !$user->hasAccess('delete', 'privacyApip'))) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to '.System::getContainer()->get('huh.request')->get('act').' privacy API app ID '.System::getContainer()->get('huh.request')->get('id').'.');
                }
                break;
            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
                $session = $objSession->all();
                if (System::getContainer()->get('huh.request')->get('act') == 'deleteAll' && !$user->hasAccess('delete', 'privacyApip')) {
                    $session['CURRENT']['IDS'] = [];
                } else {
                    $session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $root);
                }
                $objSession->replace($session);
                break;
            default:
                if (strlen(System::getContainer()->get('huh.request')->get('act'))) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to '.System::getContainer()->get('huh.request')->get('act').' privacy API apps.');
                }
                break;
        }
    }
}