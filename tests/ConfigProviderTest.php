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

namespace Mimmi20Test\LoggerFactory;

use Laminas\Log\Logger;
use Laminas\Log\LoggerInterface;
use Mimmi20\LoggerFactory\ConfigProvider;
use Mimmi20\LoggerFactory\Handler\FingersCrossed\ActivationStrategyPluginManager;
use Mimmi20\LoggerFactory\MonologFormatterPluginManager;
use Mimmi20\LoggerFactory\MonologHandlerPluginManager;
use Mimmi20\LoggerFactory\MonologPluginManager;
use Mimmi20\LoggerFactory\MonologProcessorPluginManager;
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
        self::assertCount(2, $dependencyConfig);

        self::assertArrayHasKey('factories', $dependencyConfig);
        $factories = $dependencyConfig['factories'];
        self::assertIsArray($factories);
        self::assertCount(6, $factories);
        self::assertArrayHasKey(Logger::class, $factories);
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

        self::assertArrayNotHasKey('abstract_factories', $dependencyConfig);
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

        self::assertArrayHasKey('aliases', $monologHandlerConfig);
        $aliases = $monologHandlerConfig['aliases'];
        self::assertIsArray($aliases);
        self::assertCount(56, $aliases);

        self::assertArrayHasKey('factories', $monologHandlerConfig);
        $factories = $monologHandlerConfig['factories'];
        self::assertIsArray($factories);
        self::assertCount(56, $factories);
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
        $monologFormatterConfig = $this->provider->getMonologConfig();
        self::assertIsArray($monologFormatterConfig);
        self::assertCount(1, $monologFormatterConfig);

        self::assertArrayHasKey('factories', $monologFormatterConfig);
        $factories = $monologFormatterConfig['factories'];
        self::assertIsArray($factories);
        self::assertCount(1, $factories);
        self::assertArrayHasKey(\Monolog\Logger::class, $factories);

        self::assertArrayNotHasKey('aliases', $monologFormatterConfig);
        self::assertArrayNotHasKey('abstract_factories', $monologFormatterConfig);
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
