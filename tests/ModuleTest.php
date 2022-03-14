<?php
/**
 * This file is part of the mimmi20/monolog-laminas-factory package.
 *
 * Copyright (c) 2021-2022, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20Test\LoggerFactory;

use Interop\Container\ContainerInterface;
use Laminas\ModuleManager\Listener\ServiceListenerInterface;
use Laminas\ModuleManager\ModuleEvent;
use Laminas\ModuleManager\ModuleManager;
use Mimmi20\LoggerFactory\Module;
use Mimmi20\LoggerFactory\MonologFormatterPluginManager;
use Mimmi20\LoggerFactory\MonologFormatterProviderInterface;
use Mimmi20\LoggerFactory\MonologHandlerPluginManager;
use Mimmi20\LoggerFactory\MonologHandlerProviderInterface;
use Mimmi20\LoggerFactory\MonologPluginManager;
use Mimmi20\LoggerFactory\MonologProcessorPluginManager;
use Mimmi20\LoggerFactory\MonologProcessorProviderInterface;
use Mimmi20\LoggerFactory\MonologProviderInterface;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

final class ModuleTest extends TestCase
{
    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testGetConfig(): void
    {
        $module = new Module();

        $config = $module->getConfig();

        self::assertIsArray($config);
        self::assertCount(5, $config);
        self::assertArrayHasKey('service_manager', $config);
        self::assertArrayHasKey('monolog_handlers', $config);
        self::assertArrayHasKey('monolog_processors', $config);
        self::assertArrayHasKey('monolog_formatters', $config);
        self::assertArrayHasKey('monolog', $config);
    }

    /**
     * @throws Exception
     */
    public function testInit(): void
    {
        $serviceListener = $this->getMockBuilder(ServiceListenerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceListener->expects(self::exactly(4))
            ->method('addServiceManager')
            ->withConsecutive(
                [
                    MonologPluginManager::class,
                    'monolog',
                    MonologProviderInterface::class,
                    'getMonologConfig',
                ],
                [
                    MonologHandlerPluginManager::class,
                    'monolog_handlers',
                    MonologHandlerProviderInterface::class,
                    'getMonologHandlerConfig',
                ],
                [
                    MonologProcessorPluginManager::class,
                    'monolog_processors',
                    MonologProcessorProviderInterface::class,
                    'getMonologProcessorConfig',
                ],
                [
                    MonologFormatterPluginManager::class,
                    'monolog_formatters',
                    MonologFormatterProviderInterface::class,
                    'getMonologFormatterConfig',
                ]
            );

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::once())
            ->method('get')
            ->with('ServiceListener')
            ->willReturn($serviceListener);

        $event = $this->getMockBuilder(ModuleEvent::class)
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects(self::once())
            ->method('getParam')
            ->with('ServiceManager')
            ->willReturn($container);

        $manager = $this->getMockBuilder(ModuleManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $manager->expects(self::once())
            ->method('getEvent')
            ->willReturn($event);

        $module = new Module();
        $module->init($manager);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testGetModuleDependencies(): void
    {
        $module = new Module();

        $config = $module->getModuleDependencies();

        self::assertIsArray($config);
        self::assertCount(1, $config);
        self::assertArrayHasKey(0, $config);
        self::assertContains('Laminas\Log', $config);
    }
}
