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

use Laminas\Log\LoggerInterface;
use Mimmi20\LoggerFactory\ConfigProvider;
use Mimmi20\LoggerFactory\Handler\FingersCrossed\ActivationStrategyPluginManager;
use Mimmi20\LoggerFactory\LoggerAbstractFactory;
use Mimmi20\LoggerFactory\MonologFormatterPluginManager;
use Mimmi20\LoggerFactory\MonologHandlerPluginManager;
use Mimmi20\LoggerFactory\MonologPluginManager;
use Mimmi20\LoggerFactory\MonologProcessorPluginManager;
use Monolog\Logger;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

final class ConfigProviderTest extends TestCase
{
    private ConfigProvider $provider;

    protected function setUp(): void
    {
        $this->provider = new ConfigProvider();
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testGetDependencyConfig(): void
    {
        $dependencyConfig = $this->provider->getDependencyConfig();
        self::assertIsArray($dependencyConfig);
        self::assertCount(3, $dependencyConfig);

        self::assertArrayNotHasKey('delegators', $dependencyConfig);
        self::assertArrayNotHasKey('initializers', $dependencyConfig);
        self::assertArrayNotHasKey('invokables', $dependencyConfig);
        self::assertArrayNotHasKey('services', $dependencyConfig);
        self::assertArrayNotHasKey('shared', $dependencyConfig);

        self::assertArrayHasKey('abstract_factories', $dependencyConfig);
        $abstractFactories = $dependencyConfig['abstract_factories'];
        self::assertIsArray($abstractFactories);
        self::assertContains(LoggerAbstractFactory::class, $abstractFactories);

        self::assertArrayHasKey('factories', $dependencyConfig);
        $factories = $dependencyConfig['factories'];
        self::assertIsArray($factories);
        self::assertCount(6, $factories);
        self::assertArrayHasKey(\Laminas\Log\Logger::class, $factories);
        self::assertArrayHasKey(MonologPluginManager::class, $factories);
        self::assertArrayHasKey(MonologHandlerPluginManager::class, $factories);
        self::assertArrayHasKey(MonologProcessorPluginManager::class, $factories);
        self::assertArrayHasKey(MonologFormatterPluginManager::class, $factories);
        self::assertArrayHasKey(ActivationStrategyPluginManager::class, $factories);

        self::assertArrayHasKey('aliases', $dependencyConfig);
        $aliases = $dependencyConfig['aliases'];
        self::assertIsArray($aliases);
        self::assertCount(1, $aliases);
        self::assertArrayHasKey(LoggerInterface::class, $aliases);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testGetMonologHandlerConfig(): void
    {
        $monologHandlerConfig = $this->provider->getMonologHandlerConfig();
        self::assertIsArray($monologHandlerConfig);
        self::assertCount(2, $monologHandlerConfig);

        self::assertArrayNotHasKey('abstract_factories', $monologHandlerConfig);
        self::assertArrayNotHasKey('delegators', $monologHandlerConfig);
        self::assertArrayNotHasKey('initializers', $monologHandlerConfig);
        self::assertArrayNotHasKey('invokables', $monologHandlerConfig);
        self::assertArrayNotHasKey('services', $monologHandlerConfig);
        self::assertArrayNotHasKey('shared', $monologHandlerConfig);

        self::assertArrayHasKey('aliases', $monologHandlerConfig);
        $aliases = $monologHandlerConfig['aliases'];
        self::assertIsArray($aliases);
        self::assertCount(58, $aliases);

        self::assertArrayHasKey('factories', $monologHandlerConfig);
        $factories = $monologHandlerConfig['factories'];
        self::assertIsArray($factories);
        self::assertCount(58, $factories);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testGetMonologProcessorConfig(): void
    {
        $monologProcessorConfig = $this->provider->getMonologProcessorConfig();
        self::assertIsArray($monologProcessorConfig);
        self::assertCount(2, $monologProcessorConfig);

        self::assertArrayNotHasKey('abstract_factories', $monologProcessorConfig);
        self::assertArrayNotHasKey('delegators', $monologProcessorConfig);
        self::assertArrayNotHasKey('initializers', $monologProcessorConfig);
        self::assertArrayNotHasKey('invokables', $monologProcessorConfig);
        self::assertArrayNotHasKey('services', $monologProcessorConfig);
        self::assertArrayNotHasKey('shared', $monologProcessorConfig);

        self::assertArrayHasKey('aliases', $monologProcessorConfig);
        $aliases = $monologProcessorConfig['aliases'];
        self::assertIsArray($aliases);
        self::assertCount(12, $aliases);

        self::assertArrayHasKey('factories', $monologProcessorConfig);
        $factories = $monologProcessorConfig['factories'];
        self::assertIsArray($factories);
        self::assertCount(12, $factories);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testGetMonologFormatterConfig(): void
    {
        $monologFormatterConfig = $this->provider->getMonologFormatterConfig();
        self::assertIsArray($monologFormatterConfig);
        self::assertCount(2, $monologFormatterConfig);

        self::assertArrayNotHasKey('abstract_factories', $monologFormatterConfig);
        self::assertArrayNotHasKey('delegators', $monologFormatterConfig);
        self::assertArrayNotHasKey('initializers', $monologFormatterConfig);
        self::assertArrayNotHasKey('invokables', $monologFormatterConfig);
        self::assertArrayNotHasKey('services', $monologFormatterConfig);
        self::assertArrayNotHasKey('shared', $monologFormatterConfig);

        self::assertArrayHasKey('aliases', $monologFormatterConfig);
        $aliases = $monologFormatterConfig['aliases'];
        self::assertIsArray($aliases);
        self::assertCount(16, $aliases);

        self::assertArrayHasKey('factories', $monologFormatterConfig);
        $factories = $monologFormatterConfig['factories'];
        self::assertIsArray($factories);
        self::assertCount(16, $factories);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testGetMonologConfig(): void
    {
        $monologConfig = $this->provider->getMonologConfig();
        self::assertIsArray($monologConfig);
        self::assertCount(1, $monologConfig);

        self::assertArrayNotHasKey('abstract_factories', $monologConfig);
        self::assertArrayNotHasKey('aliases', $monologConfig);
        self::assertArrayNotHasKey('delegators', $monologConfig);
        self::assertArrayNotHasKey('initializers', $monologConfig);
        self::assertArrayNotHasKey('invokables', $monologConfig);
        self::assertArrayNotHasKey('services', $monologConfig);
        self::assertArrayNotHasKey('shared', $monologConfig);

        self::assertArrayHasKey('factories', $monologConfig);
        $factories = $monologConfig['factories'];
        self::assertIsArray($factories);
        self::assertCount(1, $factories);
        self::assertArrayHasKey(Logger::class, $factories);

        self::assertArrayNotHasKey('aliases', $monologConfig);
        self::assertArrayNotHasKey('abstract_factories', $monologConfig);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testInvocationReturnsArrayWithDependencies(): void
    {
        $config = ($this->provider)();

        self::assertIsArray($config);
        self::assertCount(5, $config);
        self::assertArrayHasKey('dependencies', $config);
        self::assertArrayHasKey('monolog_handlers', $config);
        self::assertArrayHasKey('monolog_processors', $config);
        self::assertArrayHasKey('monolog_formatters', $config);
        self::assertArrayHasKey('monolog', $config);
    }
}
