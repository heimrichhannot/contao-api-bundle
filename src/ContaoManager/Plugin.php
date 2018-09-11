<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


namespace HeimrichHannot\PrivacyApiBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Config\ConfigInterface;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\ManagerPlugin\Config\ContainerBuilder;
use Contao\ManagerPlugin\Config\ExtensionPluginInterface;
use Contao\ManagerPlugin\Routing\RoutingPluginInterface;
use HeimrichHannot\PrivacyApiBundle\ContaoPrivacyApiBundle;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\HttpKernel\KernelInterface;


class Plugin implements BundlePluginInterface, RoutingPluginInterface, ExtensionPluginInterface
{

    /**
     * Gets a list of autoload configurations for this bundle.
     *
     * @param ParserInterface $parser
     *
     * @return ConfigInterface[]
     */
    public function getBundles(ParserInterface $parser)
    {
        return [
            BundleConfig::create(ContaoPrivacyApiBundle::class)->setLoadAfter(
                [
                    ContaoCoreBundle::class,
                    'privacy',
                ]
            ),
        ];
    }

    /**
     * @inheritDoc
     */
    public function getRouteCollection(LoaderResolverInterface $resolver, KernelInterface $kernel)
    {
        $file = '@ContaoPrivacyApiBundle/Resources/config/routing.yml';

        return $resolver->resolve($file)->load($file);
    }

    /**
     * @inheritDoc
     */
    public function getExtensionConfig($extensionName, array $extensionConfigs, ContainerBuilder $container)
    {
        if ('security' === $extensionName) {


            $firewalls = [
                'privacy_api' => [
                    'request_matcher' => 'huh.privacy_api.routing.matcher',
                    'stateless'       => true,
                    'guard'           => [
                        'authenticators' => ['huh.privacy_api.security.authenticator'],
                    ],
                ],
            ];

            foreach ($extensionConfigs as &$extensionConfig) {
                if (isset($extensionConfig['firewalls'])
                    && is_array($extensionConfig['firewalls'])) {
                    $extensionConfig['firewalls'] = $extensionConfig['firewalls'] + $firewalls;
                }
            }
        }

        return $extensionConfigs;
    }
}