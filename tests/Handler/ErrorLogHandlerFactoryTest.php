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

namespace Mimmi20Test\LoggerFactory\Handler;

use Interop\Container\ContainerInterface;
use Mimmi20\LoggerFactory\Handler\ErrorLogHandlerFactory;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Logger;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

final class ErrorLogHandlerFactoryTest extends TestCase
{
    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvoceWithoutConfig(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new ErrorLogHandlerFactory();

        $handler = $factory($container, '');

        self::assertInstanceOf(ErrorLogHandler::class, $handler);

        self::assertSame(Logger::DEBUG, $handler->getLevel());
        self::assertTrue($handler->getBubble());

        $mt = new ReflectionProperty($handler, 'messageType');
        $mt->setAccessible(true);

        self::assertSame(ErrorLogHandler::OPERATING_SYSTEM, $mt->getValue($handler));

        $en = new ReflectionProperty($handler, 'expandNewlines');
        $en->setAccessible(true);

        self::assertFalse($en->getValue($handler));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvoceWithEmptyConfig(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new ErrorLogHandlerFactory();

        $handler = $factory($container, '', []);

        self::assertInstanceOf(ErrorLogHandler::class, $handler);

        self::assertSame(Logger::DEBUG, $handler->getLevel());
        self::assertTrue($handler->getBubble());

        $mt = new ReflectionProperty($handler, 'messageType');
        $mt->setAccessible(true);

        self::assertSame(ErrorLogHandler::OPERATING_SYSTEM, $mt->getValue($handler));

        $en = new ReflectionProperty($handler, 'expandNewlines');
        $en->setAccessible(true);

        self::assertFalse($en->getValue($handler));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvoceWithConfig(): void
    {
        $messageType = ErrorLogHandler::SAPI;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new ErrorLogHandlerFactory();

        $handler = $factory($container, '', ['level' => LogLevel::ALERT, 'bubble' => false, 'messageType' => $messageType, 'expandNewlines' => true]);

        self::assertInstanceOf(ErrorLogHandler::class, $handler);

        self::assertSame(Logger::ALERT, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $mt = new ReflectionProperty($handler, 'messageType');
        $mt->setAccessible(true);

        self::assertSame($messageType, $mt->getValue($handler));

        $en = new ReflectionProperty($handler, 'expandNewlines');
        $en->setAccessible(true);

        self::assertTrue($en->getValue($handler));
    }
}
