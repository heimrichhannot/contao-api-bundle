<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ApiBundle\Test\DependencyInjection;

use HeimrichHannot\ApiBundle\DependencyInjection\ApiExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class ApiExtensionTest extends TestCase
{
    public function testLoad()
    {
        $extension = new ApiExtension();
        $container = new ContainerBuilder(new ParameterBag(['kernel.debug' => false]));

        $extension->load([], $container);

        $this->assertTrue($container->hasParameter('huh.api.entity.user'));
        $this->assertTrue($container->hasParameter('huh.api.entity.member'));

        $this->assertTrue($container->hasDefinition('huh.api.backend.api_app'));
        $this->assertTrue($container->hasDefinition('huh.api.routing.matcher'));
        $this->assertTrue($container->hasDefinition('huh.api.routing.login.member.matcher'));
        $this->assertTrue($container->hasDefinition('huh.api.routing.login.user.matcher'));
        $this->assertTrue($container->hasDefinition('huh.api.jwt_coder'));
        $this->assertTrue($container->hasDefinition('huh.api.security.token_authenticator'));
        $this->assertTrue($container->hasDefinition('huh.api.security.user_provider'));
        $this->assertTrue($container->hasDefinition('huh.api.security.username_password_authenticator'));
        $this->assertTrue($container->hasDefinition('huh.api.manager.resource'));
        $this->assertTrue($container->hasDefinition('huh.api.resource.member'));
    }
}
