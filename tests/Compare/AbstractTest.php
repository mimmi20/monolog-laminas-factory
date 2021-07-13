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

namespace Mimmi20Test\LoggerFactory\Compare;

use Laminas\Log\Logger;
use Laminas\Log\LoggerInterface;
use Laminas\ServiceManager\Exception\ContainerModificationsNotAllowedException;
use Laminas\ServiceManager\ServiceManager;
use Mimmi20\LoggerFactory\LoggerFactory;
use Mimmi20\LoggerFactory\MonologFormatterPluginManager;
use Mimmi20\LoggerFactory\MonologFormatterPluginManagerFactory;
use Mimmi20\LoggerFactory\MonologHandlerPluginManager;
use Mimmi20\LoggerFactory\MonologHandlerPluginManagerFactory;
use Mimmi20\LoggerFactory\MonologPluginManager;
use Mimmi20\LoggerFactory\MonologPluginManagerFactory;
use Mimmi20\LoggerFactory\MonologProcessorPluginManager;
use Mimmi20\LoggerFactory\MonologProcessorPluginManagerFactory;
use PHPUnit\Framework\TestCase;

/**
 * Base class for tests
 */
abstract class AbstractTest extends TestCase
{
    protected ServiceManager $serviceManager;

    /**
     * Prepares the environment before running a test
     *
     * @throws ContainerModificationsNotAllowedException
     */
    protected function setUp(): void
    {
        $sm = $this->serviceManager = new ServiceManager();
        $sm->setAllowOverride(true);

        $sm->setAlias(LoggerInterface::class, Logger::class);
        $sm->setFactory(Logger::class, LoggerFactory::class);
        $sm->setFactory(MonologPluginManager::class, MonologPluginManagerFactory::class);
        $sm->setFactory(MonologHandlerPluginManager::class, MonologHandlerPluginManagerFactory::class);
        $sm->setFactory(MonologProcessorPluginManager::class, MonologProcessorPluginManagerFactory::class);
        $sm->setFactory(MonologFormatterPluginManager::class, MonologFormatterPluginManagerFactory::class);

        $sm->setAllowOverride(false);
    }
}
