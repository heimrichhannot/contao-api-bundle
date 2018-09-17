<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\ApiBundle\Model;


use Contao\Model;
use Contao\System;

/**
 * Class ApiAppModel
 *
 * @package HeimrichHannot\ApiBundle\Model
 *
 * @property int    $id
 * @property int    $tstamp
 * @property string $type
 * @property string $resource
 * @property array  $resourceActions
 * @property int    $dateAdded
 * @property string $title
 * @property string $key
 * @property array  $groups
 * @property array  $mGroups
 * @property bool   $published
 * @property string $start
 * @property string $stop
 */
class ApiAppModel extends Model
{
    protected static $strTable = 'tl_api_app';

    /**
     * Find published app by key
     *
     * @param string $key
     * @param array  $options
     *
     * @return ApiAppModel|null The app models or null if there are no apps for given key
     */
    public function findPublishedByKey(string $key, array $options = [])
    {
        $t          = static::$strTable;
        $arrColumns = ["$t.key=?"];

        $time         = \Date::floorToMinute();
        $arrColumns[] = "($t.start='' OR $t.start<='$time') AND ($t.stop='' OR $t.stop>'".($time + 60)."') AND $t.published='1'";

        /** @var Model $adapter */
        $adapter = System::getContainer()->get('contao.framework')->getAdapter(static::class);

        if (null === $adapter) {
            return null;
        }

        return $adapter->findOneBy($arrColumns, [$key], $options);
    }
}