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
use Mimmi20\LoggerFactory\Handler\CubeHandlerFactory;
use Monolog\Handler\CubeHandler;
use Monolog\Logger;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

final class CubeHandlerFactoryTest extends TestCase
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

        $factory = new CubeHandlerFactory();

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

        $factory = new CubeHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('The required url is missing');

        $factory($container, '', []);
    }

    /**
     * @throws Exception
     */
    public function testInvoceWithConfig(): void
    {
        $url = true;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new CubeHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No url provided');

        $factory($container, '', ['url' => $url]);
    }

    /**
     * @throws Exception
     */
    public function testInvoceWithConfig2(): void
    {
        $url = 'ftp://test.uri';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new CubeHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Could not create a CubeHandler');

        $factory($container, '', ['url' => $url]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvoceWithConfig3(): void
    {
        $url = 'http://test.uri:80';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new CubeHandlerFactory();

        $handler = $factory($container, '', ['url' => $url]);

        self::assertInstanceOf(CubeHandler::class, $handler);

        self::assertSame(Logger::DEBUG, $handler->getLevel());
        self::assertTrue($handler->getBubble());

        $scheme = new ReflectionProperty($handler, 'scheme');
        $scheme->setAccessible(true);

        self::assertSame('http', $scheme->getValue($handler));

        $host = new ReflectionProperty($handler, 'host');
        $host->setAccessible(true);

        self::assertSame('test.uri', $host->getValue($handler));

        $port = new ReflectionProperty($handler, 'port');
        $port->setAccessible(true);

        self::assertSame(80, $port->getValue($handler));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvoceWithConfig4(): void
    {
        $url = 'http://test.uri:80';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new CubeHandlerFactory();

        $handler = $factory($container, '', ['url' => $url, 'level' => LogLevel::ALERT, 'bubble' => false]);

        self::assertInstanceOf(CubeHandler::class, $handler);

        self::assertSame(Logger::ALERT, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $scheme = new ReflectionProperty($handler, 'scheme');
        $scheme->setAccessible(true);

        self::assertSame('http', $scheme->getValue($handler));

        $host = new ReflectionProperty($handler, 'host');
        $host->setAccessible(true);

        self::assertSame('test.uri', $host->getValue($handler));

        $port = new ReflectionProperty($handler, 'port');
        $port->setAccessible(true);

        self::assertSame(80, $port->getValue($handler));
    }
}
