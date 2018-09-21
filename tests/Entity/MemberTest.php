<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ApiBundle\Test\Entity;

use Contao\MemberModel;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\ApiBundle\Entity\Member;
use HeimrichHannot\ApiBundle\Model\ApiAppModel;

class MemberTest extends ContaoTestCase
{
    /**
     * Test if isAccountNonLocked returns true while login is disabled.
     */
    public function testIsAccountNonLockedWithoutLoginAllowed()
    {
        $framework = $this->mockContaoFramework();
        $member = new Member($framework);

        $model = new \stdClass();
        $model->login = '';

        $model = $this->mockClassWithProperties(MemberModel::class, (array) $model);
        $model->method('current')->willReturn($model);

        $member->setModel($model);

        $this->assertFalse($member->isAccountNonLocked());
    }

    /**
     * Test if isAccountNonLocked returns true while login is disabled.
     */
    public function testIsAccountNonLockedWithLoginAllowed()
    {
        $framework = $this->mockContaoFramework();
        $member = new Member($framework);

        $model = new \stdClass();
        $model->login = '1';

        $model = $this->mockClassWithProperties(MemberModel::class, (array) $model);
        $model->method('current')->willReturn($model);

        $member->setModel($model);

        $this->assertTrue($member->isAccountNonLocked());
    }

    /**
     * Test hasAppAccess.
     */
    public function testHasAppAccess()
    {
        $time = time();
        $framework = $this->mockContaoFramework();

        $member = new Member($framework);

        $model = new \stdClass();
        $model->disable = '';
        $model->loginCount = '1';
        $model->lastLogin = $time - 60;
        $model->currentLogin = $time;
        $model->groups = serialize(['2', '10']);
        $model->username = 'test@test.tld';
        $model->password = 'secret';

        $model = $this->mockClassWithProperties(MemberModel::class, (array) $model);
        $model->method('current')->willReturn($model);
        $model->method('__set')->willReturnCallback(
            function ($key, $value) use (&$modifiedData) {
                $modifiedData[$key] = $value;
            }
        );

        $member->setModel($model);

        $appModel = new \stdClass();
        $appModel->title = 'APP 1';
        $appModel->mGroups = serialize(['2']);

        $appModel = $this->mockClassWithProperties(ApiAppModel::class, (array) $appModel);
        $appModel->method('current')->willReturn($appModel);

        $this->assertTrue($member->hasAppAccess($appModel));
    }

    /**
     * Test hasAppAccess without member roles.
     */
    public function testHasAppAccessWithNoUserRoles()
    {
        $framework = $this->mockContaoFramework();

        $member = new Member($framework);

        $model = new \stdClass();

        $model = $this->mockClassWithProperties(MemberModel::class, (array) $model);
        $model->method('current')->willReturn($model);
        $model->method('__set')->willReturnCallback(
            function ($key, $value) use (&$modifiedData) {
                $modifiedData[$key] = $value;
            }
        );

        $member->setModel($model);

        $appModel = new \stdClass();
        $appModel->title = 'APP 1';
        $appModel->groups = serialize(['2']);

        $appModel = $this->mockClassWithProperties(ApiAppModel::class, (array) $appModel);
        $appModel->method('current')->willReturn($appModel);

        $this->assertFalse($member->hasAppAccess($appModel));
    }

    /**
     * Test hasAppAccess without app groups defined.
     */
    public function testHasAppAccessWithNoAppGroups()
    {
        $time = time();
        $framework = $this->mockContaoFramework();

        $member = new Member($framework);

        $model = new \stdClass();
        $model->groups = serialize(['2', '10']);

        $model = $this->mockClassWithProperties(MemberModel::class, (array) $model);
        $model->method('current')->willReturn($model);
        $model->method('__set')->willReturnCallback(
            function ($key, $value) use (&$modifiedData) {
                $modifiedData[$key] = $value;
            }
        );

        $member->setModel($model);

        $appModel = new \stdClass();
        $appModel->title = 'APP 1';
        $appModel->mGroups = serialize([]);

        $appModel = $this->mockClassWithProperties(ApiAppModel::class, (array) $appModel);
        $appModel->method('current')->willReturn($appModel);

        $this->assertFalse($member->hasAppAccess($appModel));
    }

    /**
     * Test hasAppAccess without member has role of app defined.
     */
    public function testHasAppAccessWithNoMatchingGroups()
    {
        $time = time();
        $framework = $this->mockContaoFramework();

        $member = new Member($framework);

        $model = new \stdClass();
        $model->groups = serialize(['2', '10']);

        $model = $this->mockClassWithProperties(MemberModel::class, (array) $model);
        $model->method('current')->willReturn($model);
        $model->method('__set')->willReturnCallback(
            function ($key, $value) use (&$modifiedData) {
                $modifiedData[$key] = $value;
            }
        );

        $member->setModel($model);

        $appModel = new \stdClass();
        $appModel->title = 'APP 1';
        $appModel->mGroups = serialize(['11']);

        $appModel = $this->mockClassWithProperties(ApiAppModel::class, (array) $appModel);
        $appModel->method('current')->willReturn($appModel);

        $this->assertFalse($member->hasAppAccess($appModel));
    }

    /**
     * Test getModelTable().
     */
    public function testGetModelTable()
    {
        $framework = $this->mockContaoFramework();

        $user = new Member($framework);

        $this->assertEquals('tl_member', $user->getModelTable());
    }
}
