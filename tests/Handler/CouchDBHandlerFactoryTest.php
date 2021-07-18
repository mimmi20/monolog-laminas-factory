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
use Mimmi20\LoggerFactory\Handler\CouchDBHandlerFactory;
use Monolog\Handler\CouchDBHandler;
use Monolog\Logger;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

final class CouchDBHandlerFactoryTest extends TestCase
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

        $factory = new CouchDBHandlerFactory();

        $handler = $factory($container, '');

        self::assertInstanceOf(CouchDBHandler::class, $handler);

        self::assertSame(Logger::DEBUG, $handler->getLevel());
        self::assertTrue($handler->getBubble());

        $optionsP = new ReflectionProperty($handler, 'options');
        $optionsP->setAccessible(true);

        $options = $optionsP->getValue($handler);

        self::assertSame('localhost', $options['host']);
        self::assertSame(5984, $options['port']);
        self::assertSame('logger', $options['dbname']);
        self::assertNull($options['username']);
        self::assertNull($options['password']);
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

        $factory = new CouchDBHandlerFactory();

        $handler = $factory($container, '', []);

        self::assertInstanceOf(CouchDBHandler::class, $handler);

        self::assertSame(Logger::DEBUG, $handler->getLevel());
        self::assertTrue($handler->getBubble());

        $optionsP = new ReflectionProperty($handler, 'options');
        $optionsP->setAccessible(true);

        $options = $optionsP->getValue($handler);

        self::assertSame('localhost', $options['host']);
        self::assertSame(5984, $options['port']);
        self::assertSame('logger', $options['dbname']);
        self::assertNull($options['username']);
        self::assertNull($options['password']);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvoceWithConfig(): void
    {
        $level    = LogLevel::ERROR;
        $host     = 'testhost';
        $port     = 42;
        $dbname   = 'test';
        $userName = 'test-user';
        $password = 'test-password';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new CouchDBHandlerFactory();

        $handler = $factory($container, '', ['level' => $level, 'bubble' => false, 'host' => $host, 'port' => $port, 'dbname' => $dbname, 'username' => $userName, 'password' => $password]);

        self::assertInstanceOf(CouchDBHandler::class, $handler);

        self::assertSame(Logger::ERROR, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $optionsP = new ReflectionProperty($handler, 'options');
        $optionsP->setAccessible(true);

        $options = $optionsP->getValue($handler);

        self::assertSame($host, $options['host']);
        self::assertSame($port, $options['port']);
        self::assertSame($dbname, $options['dbname']);
        self::assertSame($userName, $options['username']);
        self::assertSame($password, $options['password']);
    }
}
