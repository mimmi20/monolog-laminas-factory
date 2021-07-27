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
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Mimmi20\LoggerFactory\Handler\PsrHandlerFactory;
use Monolog\Handler\PsrHandler;
use Monolog\Logger;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

final class PsrHandlerFactoryTest extends TestCase
{
    /**
     * @throws Exception
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

        $factory = new PsrHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Options must be an Array');

        $factory($container, '');
    }

    /**
     * @throws Exception
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

        $factory = new PsrHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No Service name provided for the required logger class');

        $factory($container, '', []);
    }

    /**
     * @throws Exception
     */
    public function testInvoceWithConfig(): void
    {
        $loggerName = true;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new PsrHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No Service name provided for the required logger class');

        $factory($container, '', ['logger' => $loggerName]);
    }

    /**
     * @throws Exception
     */
    public function testInvoceWithConfig2(): void
    {
        $loggerName = 'test-logger';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($loggerName)
            ->willThrowException(new ServiceNotFoundException());

        $factory = new PsrHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Could not load logger class');

        $factory($container, '', ['logger' => $loggerName]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvoceWithConfig3(): void
    {
        $loggerName = 'test-logger';
        $logger     = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($loggerName)
            ->willReturn($logger);

        $factory = new PsrHandlerFactory();

        $handler = $factory($container, '', ['logger' => $loggerName]);

        self::assertInstanceOf(PsrHandler::class, $handler);

        self::assertSame(Logger::DEBUG, $handler->getLevel());
        self::assertTrue($handler->getBubble());

        $loggerP = new ReflectionProperty($handler, 'logger');
        $loggerP->setAccessible(true);

        self::assertSame($logger, $loggerP->getValue($handler));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvoceWithConfig4(): void
    {
        $loggerName = 'test-logger';
        $logger     = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($loggerName)
            ->willReturn($logger);

        $factory = new PsrHandlerFactory();

        $handler = $factory($container, '', ['logger' => $loggerName, 'level' => LogLevel::ALERT, 'bubble' => false]);

        self::assertInstanceOf(PsrHandler::class, $handler);

        self::assertSame(Logger::ALERT, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $loggerP = new ReflectionProperty($handler, 'logger');
        $loggerP->setAccessible(true);

        self::assertSame($logger, $loggerP->getValue($handler));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvoceWithConfig5(): void
    {
        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new PsrHandlerFactory();

        $handler = $factory($container, '', ['logger' => $logger]);

        self::assertInstanceOf(PsrHandler::class, $handler);

        self::assertSame(Logger::DEBUG, $handler->getLevel());
        self::assertTrue($handler->getBubble());

        $loggerP = new ReflectionProperty($handler, 'logger');
        $loggerP->setAccessible(true);

        self::assertSame($logger, $loggerP->getValue($handler));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvoceWithConfig6(): void
    {
        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new PsrHandlerFactory();

        $handler = $factory($container, '', ['logger' => $logger, 'level' => LogLevel::ALERT, 'bubble' => false]);

        self::assertInstanceOf(PsrHandler::class, $handler);

        self::assertSame(Logger::ALERT, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $loggerP = new ReflectionProperty($handler, 'logger');
        $loggerP->setAccessible(true);

        self::assertSame($logger, $loggerP->getValue($handler));
    }
}
