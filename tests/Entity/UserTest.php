<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ApiBundle\Test\Entity;

use Contao\Config;
use Contao\Date;
use Contao\StringUtil;
use Contao\TestCase\ContaoTestCase;
use Contao\UserModel;
use HeimrichHannot\ApiBundle\Entity\User;
use HeimrichHannot\ApiBundle\Model\ApiAppModel;

class UserTest extends ContaoTestCase
{
    /**
     * Test isAccountNonExpired() should return false if account inactive due to expired stop date.
     */
    public function testIsAccountExpiredByStop()
    {
        $time = time();
        $lockPeriod = 300; //period of time an account is locked (default: 5 minutes)

        $configAdapter = $this->mockAdapter(['get']);
        $configAdapter->method('get')->willReturn($lockPeriod);

        $dateAdapter = $this->mockAdapter(['floorToMinute']);
        $dateAdapter->method('floorToMinute')->willReturn($time - ($time % 60));

        $framework = $this->mockContaoFramework(
            [
                Config::class => $configAdapter,
                Date::class => $dateAdapter,
            ]
        );

        $user = new User($framework);

        $model = new \stdClass();
        $model->start = $time;
        $model->stop = $time - 1;

        $model = $this->mockClassWithProperties(UserModel::class, (array) $model);
        $model->method('current')->willReturn($model);

        $user->setModel($model);

        $this->assertFalse($user->isAccountNonExpired());
    }

    /**
     * Test isAccountNonExpired() should return false if account inactive due to expired start date.
     */
    public function testIsAccountExpiredByStart()
    {
        $time = time();
        $lockPeriod = 300; //period of time an account is locked (default: 5 minutes)

        $configAdapter = $this->mockAdapter(['get']);
        $configAdapter->method('get')->willReturn($lockPeriod);

        $dateAdapter = $this->mockAdapter(['floorToMinute']);
        $dateAdapter->method('floorToMinute')->willReturn($time - ($time % 60));

        $framework = $this->mockContaoFramework(
            [
                Config::class => $configAdapter,
                Date::class => $dateAdapter,
            ]
        );

        $user = new User($framework);

        $model = new \stdClass();
        $model->start = '';
        $model->stop = $time - 120;

        $model = $this->mockClassWithProperties(UserModel::class, (array) $model);
        $model->method('current')->willReturn($model);

        $user->setModel($model);

        $this->assertFalse($user->isAccountNonExpired());
    }

    /**
     * Test isAccountNonExpired().
     */
    public function testIsAccountNonExpired()
    {
        $time = time();
        $lockPeriod = 300; //period of time an account is locked (default: 5 minutes)

        $configAdapter = $this->mockAdapter(['get']);
        $configAdapter->method('get')->willReturn($lockPeriod);

        $dateAdapter = $this->mockAdapter(['floorToMinute']);
        $dateAdapter->method('floorToMinute')->willReturn($time - ($time % 60));

        $framework = $this->mockContaoFramework(
            [
                Config::class => $configAdapter,
                Date::class => $dateAdapter,
            ]
        );

        $user = new User($framework);

        $model = new \stdClass();
        $model->start = '';
        $model->stop = $time + 100;

        $model = $this->mockClassWithProperties(UserModel::class, (array) $model);
        $model->method('current')->willReturn($model);

        $user->setModel($model);

        $this->assertTrue($user->isAccountNonExpired());
    }

    /**
     * Test isAccountNonLocked() should return false if account is locked indeed.
     */
    public function testAccountLocked()
    {
        $time = time();
        $lockPeriod = 300; //period of time an account is locked (default: 5 minutes)

        $configAdapter = $this->mockAdapter(['get']);
        $configAdapter->method('get')->willReturn($lockPeriod);

        $framework = $this->mockContaoFramework(
            [
                Config::class => $configAdapter,
            ]
        );

        $user = new User($framework);

        $model = new \stdClass();
        $model->locked = $time - $lockPeriod + 50;

        $model = $this->mockClassWithProperties(UserModel::class, (array) $model);
        $model->method('current')->willReturn($model);

        $user->setModel($model);

        $this->assertFalse($user->isAccountNonLocked());
    }

    /**
     * Test isEnabled() should return false if account is disabled.
     */
    public function testIsEnabledWhileDisabled()
    {
        $lockPeriod = 300; //period of time an account is locked (default: 5 minutes)

        $configAdapter = $this->mockAdapter(['get']);
        $configAdapter->method('get')->willReturn($lockPeriod);

        $framework = $this->mockContaoFramework(
            [
                Config::class => $configAdapter,
            ]
        );

        $user = new User($framework);

        $model = new \stdClass();
        $model->disable = 1;

        $model = $this->mockClassWithProperties(UserModel::class, (array) $model);
        $model->method('current')->willReturn($model);

        $user->setModel($model);

        $this->assertFalse($user->isEnabled());
    }

    /**
     * Test isEnabled() should return false if account is disabled.
     */
    public function testIsEnabled()
    {
        $framework = $this->mockContaoFramework();

        $user = new User($framework);

        $model = new \stdClass();
        $model->disable = '';

        $model = $this->mockClassWithProperties(UserModel::class, (array) $model);
        $model->method('current')->willReturn($model);

        $user->setModel($model);

        $this->assertTrue($user->isEnabled());
    }

    /**
     * Test setters and getters.
     */
    public function testSetterAndGetter()
    {
        $time = time();
        $framework = $this->mockContaoFramework();

        $user = new User($framework);

        $modifiedData = [];

        $model = new \stdClass();
        $model->disable = '';
        $model->loginCount = '1';
        $model->lastLogin = $time - 60;
        $model->currentLogin = $time;
        $model->groups = serialize(['2', '10']);
        $model->username = 'test@test.tld';
        $model->password = 'secret';

        $model = $this->mockClassWithProperties(UserModel::class, (array) $model);
        $model->method('current')->willReturn($model);
        $model->method('__set')->willReturnCallback(
            function ($key, $value) use (&$modifiedData) {
                $modifiedData[$key] = $value;
            }
        );

        $user->setModel($model);
        $this->assertSame($user->getModel(), $model);

        $this->assertSame(1, $user->getLoginCount());
        $user->setLoginCount(2);
        $this->assertSame(2, $modifiedData['loginCount']);

        $this->assertSame($model->lastLogin, $user->getLastLogin());
        $user->setLastLogin($time - 100);
        $this->assertSame($time - 100, $modifiedData['lastLogin']);

        $this->assertSame($model->currentLogin, $user->getCurrentLogin());
        $user->setCurrentLogin($time + 1);
        $this->assertSame($time + 1, $modifiedData['currentLogin']);

        $this->assertSame(StringUtil::deserialize($model->groups, true), $user->getRoles());

        $this->assertTrue($user->isCredentialsNonExpired());

        // eraseCredentials() should not do anything right now
        $data = $modifiedData;
        $user->eraseCredentials();
        $this->assertSame($modifiedData, $data);

        $this->assertSame($model->username, $user->getUsername());
        $this->assertSame($model->password, $user->getPassword());
        $this->assertNull($user->getSalt());

        $appModel = new \stdClass();
        $appModel->title = 'APP 1';

        $appModel = $this->mockClassWithProperties(ApiAppModel::class, (array) $appModel);
        $appModel->method('current')->willReturn($appModel);

        $user->setApp($appModel);
        $this->assertSame($appModel, $user->getApp());
    }

    /**
     * Test findBy.
     */
    public function testFindByUsername()
    {
        $model = new \stdClass();
        $model->username = 'test@test.tld';

        $model = $this->mockClassWithProperties(UserModel::class, (array) $model);
        $model->method('current')->willReturn($model);
        $model->method('__set')->willReturnCallback(
            function ($key, $value) use (&$modifiedData) {
                $modifiedData[$key] = $value;
            }
        );

        $userModelAdapter = $this->mockAdapter(['findBy']);
        $userModelAdapter->method('findBy')->willReturn($model);

        $framework = $this->mockContaoFramework([UserModel::class => $userModelAdapter]);

        $GLOBALS['TL_MODELS']['tl_user'] = 'Contao\UserModel';

        $user = new User($framework);
        $this->assertEquals($model->username, $user->findBy('username', 'test@test.tld')->getUsername());
    }

    /**
     * Test findBy.
     *
     * @runInSeparateProcess
     */
    public function testFindByUsernameWithoutModelClass()
    {
        $model = new \stdClass();
        $model->username = 'test@test.tld';

        $model = $this->mockClassWithProperties(UserModel::class, (array) $model);
        $model->method('current')->willReturn($model);
        $model->method('__set')->willReturnCallback(
            function ($key, $value) use (&$modifiedData) {
                $modifiedData[$key] = $value;
            }
        );

        $userModelAdapter = $this->mockAdapter(['findBy']);
        $userModelAdapter->method('findBy')->willReturn($model);

        $framework = $this->mockContaoFramework([UserModel::class => $userModelAdapter]);

        $GLOBALS['TL_MODELS']['tl_user'] = 'UserModelThatDoesNotExist';

        $user = new User($framework);
        $this->assertNull($user->findBy('username', 'test@test.tld'));
    }

    /**
     * Test hasAppAccess.
     */
    public function testHasAppAccess()
    {
        $time = time();
        $framework = $this->mockContaoFramework();

        $user = new User($framework);

        $model = new \stdClass();
        $model->disable = '';
        $model->loginCount = '1';
        $model->lastLogin = $time - 60;
        $model->currentLogin = $time;
        $model->groups = serialize(['2', '10']);
        $model->username = 'test@test.tld';
        $model->password = 'secret';

        $model = $this->mockClassWithProperties(UserModel::class, (array) $model);
        $model->method('current')->willReturn($model);
        $model->method('__set')->willReturnCallback(
            function ($key, $value) use (&$modifiedData) {
                $modifiedData[$key] = $value;
            }
        );

        $user->setModel($model);

        $appModel = new \stdClass();
        $appModel->title = 'APP 1';
        $appModel->groups = serialize(['2']);

        $appModel = $this->mockClassWithProperties(ApiAppModel::class, (array) $appModel);
        $appModel->method('current')->willReturn($appModel);

        $this->assertTrue($user->hasAppAccess($appModel));
    }

    /**
     * Test hasAppAccess with admin role.
     */
    public function testAdminHasAppAccess()
    {
        $framework = $this->mockContaoFramework();

        $user = new User($framework);

        $model = new \stdClass();
        $model->admin = '1';

        $model = $this->mockClassWithProperties(UserModel::class, (array) $model);
        $model->method('current')->willReturn($model);
        $model->method('__set')->willReturnCallback(
            function ($key, $value) use (&$modifiedData) {
                $modifiedData[$key] = $value;
            }
        );

        $user->setModel($model);

        $appModel = new \stdClass();
        $appModel->title = 'APP 1';
        $appModel->groups = serialize(['2']);

        $appModel = $this->mockClassWithProperties(ApiAppModel::class, (array) $appModel);
        $appModel->method('current')->willReturn($appModel);

        $this->assertTrue($user->hasAppAccess($appModel));
    }

    /**
     * Test hasAppAccess without user roles.
     */
    public function testHasAppAccessWithNoUserRoles()
    {
        $framework = $this->mockContaoFramework();

        $user = new User($framework);

        $model = new \stdClass();

        $model = $this->mockClassWithProperties(UserModel::class, (array) $model);
        $model->method('current')->willReturn($model);
        $model->method('__set')->willReturnCallback(
            function ($key, $value) use (&$modifiedData) {
                $modifiedData[$key] = $value;
            }
        );

        $user->setModel($model);

        $appModel = new \stdClass();
        $appModel->title = 'APP 1';
        $appModel->groups = serialize(['2']);

        $appModel = $this->mockClassWithProperties(ApiAppModel::class, (array) $appModel);
        $appModel->method('current')->willReturn($appModel);

        $this->assertFalse($user->hasAppAccess($appModel));
    }

    /**
     * Test hasAppAccess without app groups defined.
     */
    public function testHasAppAccessWithNoAppGroups()
    {
        $framework = $this->mockContaoFramework();

        $user = new User($framework);

        $model = new \stdClass();
        $model->groups = serialize(['2', '10']);

        $model = $this->mockClassWithProperties(UserModel::class, (array) $model);
        $model->method('current')->willReturn($model);
        $model->method('__set')->willReturnCallback(
            function ($key, $value) use (&$modifiedData) {
                $modifiedData[$key] = $value;
            }
        );

        $user->setModel($model);

        $appModel = new \stdClass();
        $appModel->title = 'APP 1';
        $appModel->groups = serialize([]);

        $appModel = $this->mockClassWithProperties(ApiAppModel::class, (array) $appModel);
        $appModel->method('current')->willReturn($appModel);

        $this->assertFalse($user->hasAppAccess($appModel));
    }

    /**
     * Test hasAppAccess without user has role of app defined.
     */
    public function testHasAppAccessWithNoMatchingGroups()
    {
        $framework = $this->mockContaoFramework();

        $user = new User($framework);

        $model = new \stdClass();
        $model->groups = serialize(['2', '10']);

        $model = $this->mockClassWithProperties(UserModel::class, (array) $model);
        $model->method('current')->willReturn($model);
        $model->method('__set')->willReturnCallback(
            function ($key, $value) use (&$modifiedData) {
                $modifiedData[$key] = $value;
            }
        );

        $user->setModel($model);

        $appModel = new \stdClass();
        $appModel->title = 'APP 1';
        $appModel->groups = serialize(['11']);

        $appModel = $this->mockClassWithProperties(ApiAppModel::class, (array) $appModel);
        $appModel->method('current')->willReturn($appModel);

        $this->assertFalse($user->hasAppAccess($appModel));
    }
}
