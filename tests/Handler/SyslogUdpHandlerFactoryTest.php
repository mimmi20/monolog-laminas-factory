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
use Mimmi20\LoggerFactory\Handler\SyslogUdpHandlerFactory;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Logger;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

use function sprintf;

use const LOG_MAIL;
use const LOG_USER;

final class SyslogUdpHandlerFactoryTest extends TestCase
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

        $factory = new SyslogUdpHandlerFactory();

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

        $factory = new SyslogUdpHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No host provided');

        $factory($container, '', []);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     *
     * @requires extension sockets
     */
    public function testInvoceWithConfig(): void
    {
        $host = 'test-host';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new SyslogUdpHandlerFactory();

        $handler = $factory($container, '', ['host' => $host]);

        self::assertInstanceOf(SyslogUdpHandler::class, $handler);

        self::assertSame(Logger::DEBUG, $handler->getLevel());
        self::assertTrue($handler->getBubble());

        $identP = new ReflectionProperty($handler, 'ident');
        $identP->setAccessible(true);

        self::assertSame('php', $identP->getValue($handler));

        $rfcP = new ReflectionProperty($handler, 'rfc');
        $rfcP->setAccessible(true);

        self::assertSame(SyslogUdpHandler::RFC5424, $rfcP->getValue($handler));

        $fa = new ReflectionProperty($handler, 'facility');
        $fa->setAccessible(true);

        self::assertSame(LOG_USER, $fa->getValue($handler));

        $socketP = new ReflectionProperty($handler, 'socket');
        $socketP->setAccessible(true);

        $socket = $socketP->getValue($handler);

        $ipP = new ReflectionProperty($socket, 'ip');
        $ipP->setAccessible(true);

        self::assertSame($host, $ipP->getValue($socket));

        $portP = new ReflectionProperty($socket, 'port');
        $portP->setAccessible(true);

        self::assertSame(514, $portP->getValue($socket));

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
     *
     * @requires extension sockets
     */
    public function testInvoceWithConfig2(): void
    {
        $host     = 'test-host';
        $port     = 4711;
        $facility = LOG_MAIL;
        $ident    = 'test-ident';
        $rfc      = SyslogUdpHandler::RFC3164;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new SyslogUdpHandlerFactory();

        $handler = $factory($container, '', ['host' => $host, 'port' => $port, 'facility' => $facility, 'level' => LogLevel::ALERT, 'bubble' => false, 'ident' => $ident, 'rfc' => $rfc]);

        self::assertInstanceOf(SyslogUdpHandler::class, $handler);

        self::assertSame(Logger::ALERT, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $identP = new ReflectionProperty($handler, 'ident');
        $identP->setAccessible(true);

        self::assertSame($ident, $identP->getValue($handler));

        $rfcP = new ReflectionProperty($handler, 'rfc');
        $rfcP->setAccessible(true);

        self::assertSame($rfc, $rfcP->getValue($handler));

        $fa = new ReflectionProperty($handler, 'facility');
        $fa->setAccessible(true);

        self::assertSame($facility, $fa->getValue($handler));

        $socketP = new ReflectionProperty($handler, 'socket');
        $socketP->setAccessible(true);

        $socket = $socketP->getValue($handler);

        $ipP = new ReflectionProperty($socket, 'ip');
        $ipP->setAccessible(true);

        self::assertSame($host, $ipP->getValue($socket));

        $portP = new ReflectionProperty($socket, 'port');
        $portP->setAccessible(true);

        self::assertSame($port, $portP->getValue($socket));

        self::assertInstanceOf(LineFormatter::class, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');
        $proc->setAccessible(true);

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     *
     * @requires extension sockets
     */
    public function testInvoceWithConfigAndBoolFormatter(): void
    {
        $host      = 'test-host';
        $port      = 4711;
        $facility  = LOG_MAIL;
        $ident     = 'test-ident';
        $rfc       = SyslogUdpHandler::RFC3164;
        $formatter = true;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new SyslogUdpHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class)
        );

        $factory($container, '', ['host' => $host, 'port' => $port, 'facility' => $facility, 'level' => LogLevel::ALERT, 'bubble' => false, 'ident' => $ident, 'rfc' => $rfc, 'formatter' => $formatter]);
    }
}
