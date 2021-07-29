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
use Mimmi20\LoggerFactory\Handler\PushoverHandlerFactory;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\PushoverHandler;
use Monolog\Logger;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

use function gethostname;
use function sprintf;

final class PushoverHandlerFactoryTest extends TestCase
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

        $factory = new PushoverHandlerFactory();

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

        $factory = new PushoverHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No token provided');

        $factory($container, '', []);
    }

    /**
     * @throws Exception
     */
    public function testInvoceWithConfigWithoutUsers(): void
    {
        $token = 'token';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new PushoverHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No users provided');

        $factory($container, '', ['token' => $token]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvoceWithConfigAndUsers(): void
    {
        $token = 'token';
        $users = 'abc';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new PushoverHandlerFactory();

        $handler = $factory($container, '', ['token' => $token, 'users' => $users]);

        self::assertInstanceOf(PushoverHandler::class, $handler);

        self::assertSame(Logger::DEBUG, $handler->getLevel());
        self::assertTrue($handler->getBubble());
        self::assertSame('ssl://api.pushover.net:443', $handler->getConnectionString());
        self::assertSame(60.0, $handler->getTimeout());
        self::assertSame(60.0, $handler->getWritingTimeout());
        self::assertSame(60.0, $handler->getConnectionTimeout());
        //self::assertSame(0, $handler->getChunkSize());
        self::assertFalse($handler->isPersistent());

        $tk = new ReflectionProperty($handler, 'token');
        $tk->setAccessible(true);

        self::assertSame($token, $tk->getValue($handler));

        $us = new ReflectionProperty($handler, 'users');
        $us->setAccessible(true);

        self::assertSame([$users], $us->getValue($handler));

        $ti = new ReflectionProperty($handler, 'title');
        $ti->setAccessible(true);

        self::assertSame((string) gethostname(), $ti->getValue($handler));

        $hpl = new ReflectionProperty($handler, 'highPriorityLevel');
        $hpl->setAccessible(true);

        self::assertSame(Logger::CRITICAL, $hpl->getValue($handler));

        $el = new ReflectionProperty($handler, 'emergencyLevel');
        $el->setAccessible(true);

        self::assertSame(Logger::EMERGENCY, $el->getValue($handler));

        $re = new ReflectionProperty($handler, 'retry');
        $re->setAccessible(true);

        self::assertSame(30, $re->getValue($handler));

        $ex = new ReflectionProperty($handler, 'expire');
        $ex->setAccessible(true);

        self::assertSame(25200, $ex->getValue($handler));

        self::assertInstanceOf(LineFormatter::class, $handler->getFormatter());

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
    public function testInvoceWithConfigAndUsers2(): void
    {
        $token        = 'token';
        $users        = ['abc', 'xyz'];
        $title        = 'title';
        $retry        = 24;
        $expire       = 42;
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

        $factory = new PushoverHandlerFactory();

        $handler = $factory($container, '', ['token' => $token, 'users' => $users, 'title' => $title, 'level' => LogLevel::ALERT, 'bubble' => false, 'useSSL' => false, 'highPriorityLevel' => LogLevel::ERROR, 'emergencyLevel' => LogLevel::ALERT, 'retry' => $retry, 'expire' => $expire, 'timeout' => $timeout, 'writeTimeout' => $writeTimeout, 'persistent' => $persistent, 'chunkSize' => $chunkSize]);

        self::assertInstanceOf(PushoverHandler::class, $handler);

        self::assertSame(Logger::ALERT, $handler->getLevel());
        self::assertFalse($handler->getBubble());
        self::assertSame('api.pushover.net:80', $handler->getConnectionString());
        self::assertSame($writeTimeout, $handler->getTimeout());
        self::assertSame($writeTimeout, $handler->getWritingTimeout());
        self::assertSame($timeout, $handler->getConnectionTimeout());
        self::assertSame($chunkSize, $handler->getChunkSize());
        self::assertTrue($handler->isPersistent());

        $tk = new ReflectionProperty($handler, 'token');
        $tk->setAccessible(true);

        self::assertSame($token, $tk->getValue($handler));

        $us = new ReflectionProperty($handler, 'users');
        $us->setAccessible(true);

        self::assertSame($users, $us->getValue($handler));

        $ti = new ReflectionProperty($handler, 'title');
        $ti->setAccessible(true);

        self::assertSame($title, $ti->getValue($handler));

        $hpl = new ReflectionProperty($handler, 'highPriorityLevel');
        $hpl->setAccessible(true);

        self::assertSame(Logger::ERROR, $hpl->getValue($handler));

        $el = new ReflectionProperty($handler, 'emergencyLevel');
        $el->setAccessible(true);

        self::assertSame(Logger::ALERT, $el->getValue($handler));

        $re = new ReflectionProperty($handler, 'retry');
        $re->setAccessible(true);

        self::assertSame($retry, $re->getValue($handler));

        $ex = new ReflectionProperty($handler, 'expire');
        $ex->setAccessible(true);

        self::assertSame($expire, $ex->getValue($handler));

        self::assertInstanceOf(LineFormatter::class, $handler->getFormatter());

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
        $users        = ['abc', 'xyz'];
        $title        = 'title';
        $retry        = 24;
        $expire       = 42;
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

        $factory = new PushoverHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class)
        );

        $factory($container, '', ['token' => $token, 'users' => $users, 'title' => $title, 'level' => LogLevel::ALERT, 'bubble' => false, 'useSSL' => false, 'highPriorityLevel' => LogLevel::ERROR, 'emergencyLevel' => LogLevel::ALERT, 'retry' => $retry, 'expire' => $expire, 'timeout' => $timeout, 'writeTimeout' => $writeTimeout, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'formatter' => $formatter]);
    }
}
