<?php
/**
 * This file is part of the mimmi20/monolog-laminas-factory package.
 *
 * Copyright (c) 2021, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20\LoggerFactory;

use Laminas\ModuleManager\Feature\ConfigProviderInterface;
use Laminas\ModuleManager\Feature\InitProviderInterface;
use Laminas\ModuleManager\Listener\ServiceListenerInterface;
use Laminas\ModuleManager\ModuleManagerInterface;

use function assert;

final class Module implements ConfigProviderInterface, InitProviderInterface
{
    /**
     * Return default configuration for laminas-mvc applications.
     *
     * @return array<string, array<string, array<int|string, string>>>
     */
    public function getConfig(): array
    {
        $provider = new ConfigProvider();

        return [
            'service_manager' => $provider->getDependencyConfig(),
            'monolog' => $provider->getMonologConfig(),
            'monolog_handlers' => $provider->getMonologHandlerConfig(),
            'monolog_processors' => $provider->getMonologProcessorConfig(),
            'monolog_formatters' => $provider->getMonologFormatterConfig(),
        ];
    }

    /**
     * Register specifications for all plugin managers with the ServiceListener.
     */
    public function init(ModuleManagerInterface $manager): void
    {
        $event     = $manager->getEvent();
        $container = $event->getParam('ServiceManager');

        $serviceListener = $container->get('ServiceListener');
        assert($serviceListener instanceof ServiceListenerInterface);

        $serviceListener->addServiceManager(
            MonologPluginManager::class,
            'monolog',
            MonologProviderInterface::class,
            'getMonologConfig'
        );

        $serviceListener->addServiceManager(
            MonologHandlerPluginManager::class,
            'monolog_handlers',
            MonologHandlerProviderInterface::class,
            'getMonologHandlerConfig'
        );

        $serviceListener->addServiceManager(
            MonologProcessorPluginManager::class,
            'monolog_processors',
            MonologProcessorProviderInterface::class,
            'getMonologProcessorConfig'
        );

        $serviceListener->addServiceManager(
            MonologFormatterPluginManager::class,
            'monolog_formatters',
            MonologFormatterProviderInterface::class,
            'getMonologFormatterConfig'
        );
    }
}
