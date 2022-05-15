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

namespace Mimmi20Test\LoggerFactory\Processor;

use Mimmi20\LoggerFactory\Processor\GitProcessorFactory;
use Monolog\Logger;
use Monolog\Processor\GitProcessor;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

final class GitProcessorFactoryTest extends TestCase
{
    /**
     * @throws Exception
     * @throws ReflectionException
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

        $factory = new GitProcessorFactory();

        $processor = $factory($container, '');

        self::assertInstanceOf(GitProcessor::class, $processor);

        $lvl = new ReflectionProperty($processor, 'level');
        $lvl->setAccessible(true);

        self::assertSame(Logger::DEBUG, $lvl->getValue($processor));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvokeWithEmptyConfig(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new GitProcessorFactory();

        $processor = $factory($container, '', []);

        self::assertInstanceOf(GitProcessor::class, $processor);

        $lvl = new ReflectionProperty($processor, 'level');
        $lvl->setAccessible(true);

        self::assertSame(Logger::DEBUG, $lvl->getValue($processor));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvokeWithLevel(): void
    {
        $level = LogLevel::ALERT;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new GitProcessorFactory();

        $processor = $factory($container, '', ['level' => $level]);

        self::assertInstanceOf(GitProcessor::class, $processor);

        $lvl = new ReflectionProperty($processor, 'level');
        $lvl->setAccessible(true);

        self::assertSame(Logger::ALERT, $lvl->getValue($processor));
    }
}
