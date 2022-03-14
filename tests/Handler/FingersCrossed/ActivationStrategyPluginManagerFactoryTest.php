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

namespace Mimmi20Test\LoggerFactory\Handler\FingersCrossed;

use Interop\Container\ContainerInterface;
use Mimmi20\LoggerFactory\Handler\FingersCrossed\ActivationStrategyPluginManager;
use Mimmi20\LoggerFactory\Handler\FingersCrossed\ActivationStrategyPluginManagerFactory;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

final class ActivationStrategyPluginManagerFactoryTest extends TestCase
{
    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testInvokeWithoutConfig(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new ActivationStrategyPluginManagerFactory();

        $pluginManager = $factory($container, '');

        self::assertInstanceOf(ActivationStrategyPluginManager::class, $pluginManager);
    }
}
