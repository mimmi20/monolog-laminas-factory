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
use Mimmi20\LoggerFactory\Handler\LogmaticHandlerFactory;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LogmaticFormatter;
use Monolog\Handler\LogmaticHandler;
use Monolog\Logger;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

use function sprintf;

final class LogmaticHandlerFactoryTest extends TestCase
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

        $factory = new LogmaticHandlerFactory();

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

        $factory = new LogmaticHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No token provided');

        $factory($container, '', []);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvoceWithConfig(): void
    {
        $token = 'token';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new LogmaticHandlerFactory();

        $handler = $factory($container, '', ['token' => $token]);

        self::assertInstanceOf(LogmaticHandler::class, $handler);

        self::assertSame(Logger::DEBUG, $handler->getLevel());
        self::assertTrue($handler->getBubble());
        self::assertSame('ssl://api.logmatic.io:10515/v1/', $handler->getConnectionString());
        self::assertSame(60.0, $handler->getTimeout());
        self::assertSame(60.0, $handler->getWritingTimeout());
        self::assertSame(60.0, $handler->getConnectionTimeout());
        //self::assertSame(0, $handler->getChunkSize());
        self::assertFalse($handler->isPersistent());

        $lt = new ReflectionProperty($handler, 'logToken');
        $lt->setAccessible(true);

        self::assertSame($token, $lt->getValue($handler));

        $hn = new ReflectionProperty($handler, 'hostname');
        $hn->setAccessible(true);

        self::assertSame('', $hn->getValue($handler));

        $an = new ReflectionProperty($handler, 'appname');
        $an->setAccessible(true);

        self::assertSame('', $an->getValue($handler));

        self::assertInstanceOf(LogmaticFormatter::class, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');
        $proc->setAccessible(true);

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvoceWithConfig2(): void
    {
        $token        = 'token';
        $hostname     = 'test-host';
        $appname      = 'test-app';
        $timeout      = 42.0;
        $writeTimeout = 120.0;
        $persistent   = true;
        $chunkSize    = 100;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new LogmaticHandlerFactory();

        $handler = $factory($container, '', ['token' => $token, 'hostname' => $hostname, 'appname' => $appname, 'useSSL' => false, 'level' => LogLevel::ALERT, 'bubble' => false, 'timeout' => $timeout, 'writeTimeout' => $writeTimeout, 'persistent' => $persistent, 'chunkSize' => $chunkSize]);

        self::assertInstanceOf(LogmaticHandler::class, $handler);

        self::assertSame(Logger::ALERT, $handler->getLevel());
        self::assertFalse($handler->getBubble());
        self::assertSame('api.logmatic.io:10514/v1/', $handler->getConnectionString());
        self::assertSame($writeTimeout, $handler->getTimeout());
        self::assertSame($writeTimeout, $handler->getWritingTimeout());
        self::assertSame($timeout, $handler->getConnectionTimeout());
        self::assertSame($chunkSize, $handler->getChunkSize());
        self::assertTrue($handler->isPersistent());

        $lt = new ReflectionProperty($handler, 'logToken');
        $lt->setAccessible(true);

        self::assertSame($token, $lt->getValue($handler));

        $hn = new ReflectionProperty($handler, 'hostname');
        $hn->setAccessible(true);

        self::assertSame($hostname, $hn->getValue($handler));

        $an = new ReflectionProperty($handler, 'appname');
        $an->setAccessible(true);

        self::assertSame($appname, $an->getValue($handler));

        self::assertInstanceOf(LogmaticFormatter::class, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');
        $proc->setAccessible(true);

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     */
    public function testInvoceWithConfigAndBoolFormatter(): void
    {
        $token        = 'token';
        $hostname     = 'test-host';
        $appname      = 'test-app';
        $timeout      = 42.0;
        $writeTimeout = 120.0;
        $persistent   = true;
        $chunkSize    = 100;
        $formatter    = true;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new LogmaticHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class)
        );

        $factory($container, '', ['token' => $token, 'hostname' => $hostname, 'appname' => $appname, 'useSSL' => false, 'level' => LogLevel::ALERT, 'bubble' => false, 'timeout' => $timeout, 'writeTimeout' => $writeTimeout, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'formatter' => $formatter]);
    }
}
