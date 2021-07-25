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
use Mimmi20\LoggerFactory\Handler\SocketHandlerFactory;
use Monolog\Handler\SocketHandler;
use Monolog\Logger;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

final class SocketHandlerFactoryTest extends TestCase
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

        $factory = new SocketHandlerFactory();

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

        $factory = new SocketHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No connectionString provided');

        $factory($container, '', []);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testInvoceWithConfig(): void
    {
        $connectionString = 'conn-string';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new SocketHandlerFactory();

        $handler = $factory($container, '', ['connectionString' => $connectionString]);

        self::assertInstanceOf(SocketHandler::class, $handler);

        self::assertSame(Logger::DEBUG, $handler->getLevel());
        self::assertTrue($handler->getBubble());
        self::assertSame($connectionString, $handler->getConnectionString());
        self::assertSame(60.0, $handler->getTimeout());
        self::assertSame(60.0, $handler->getWritingTimeout());
        self::assertSame(60.0, $handler->getConnectionTimeout());
        //self::assertSame(0, $handler->getChunkSize());
        self::assertFalse($handler->isPersistent());
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testInvoceWithConfig2(): void
    {
        $connectionString = 'conn-string';
        $timeout          = 42.0;
        $writeTimeout     = 120.0;
        $level            = LogLevel::ALERT;
        $bubble           = false;
        $persistent       = true;
        $chunkSize        = 100;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new SocketHandlerFactory();

        $handler = $factory($container, '', ['connectionString' => $connectionString, 'timeout' => $timeout, 'writeTimeout' => $writeTimeout, 'level' => $level, 'bubble' => $bubble, 'persistent' => $persistent, 'chunkSize' => $chunkSize]);

        self::assertInstanceOf(SocketHandler::class, $handler);

        self::assertSame(Logger::ALERT, $handler->getLevel());
        self::assertFalse($handler->getBubble());
        self::assertSame($connectionString, $handler->getConnectionString());
        self::assertSame($writeTimeout, $handler->getTimeout());
        self::assertSame($writeTimeout, $handler->getWritingTimeout());
        self::assertSame($timeout, $handler->getConnectionTimeout());
        self::assertSame($chunkSize, $handler->getChunkSize());
        self::assertTrue($handler->isPersistent());
    }
}
