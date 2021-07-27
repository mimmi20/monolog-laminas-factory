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
use Mimmi20\LoggerFactory\Handler\SyslogHandlerFactory;
use Monolog\Handler\SyslogHandler;
use Monolog\Logger;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

use const LOG_CONS;
use const LOG_MAIL;
use const LOG_PID;
use const LOG_USER;

final class SyslogHandlerFactoryTest extends TestCase
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

        $factory = new SyslogHandlerFactory();

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

        $factory = new SyslogHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No ident provided');

        $factory($container, '', []);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvoceWithConfig(): void
    {
        $ident = 'test';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new SyslogHandlerFactory();

        $handler = $factory($container, '', ['ident' => $ident]);

        self::assertInstanceOf(SyslogHandler::class, $handler);

        self::assertSame(Logger::DEBUG, $handler->getLevel());
        self::assertTrue($handler->getBubble());

        $identP = new ReflectionProperty($handler, 'ident');
        $identP->setAccessible(true);

        self::assertSame($ident, $identP->getValue($handler));

        $lo = new ReflectionProperty($handler, 'logopts');
        $lo->setAccessible(true);

        self::assertSame(LOG_PID, $lo->getValue($handler));

        $fa = new ReflectionProperty($handler, 'facility');
        $fa->setAccessible(true);

        self::assertSame(LOG_USER, $fa->getValue($handler));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvoceWithConfig2(): void
    {
        $ident    = 'test';
        $facility = LOG_MAIL;
        $logOpts  = LOG_CONS;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new SyslogHandlerFactory();

        $handler = $factory($container, '', ['ident' => $ident, 'facility' => $facility, 'level' => LogLevel::ALERT, 'bubble' => false, 'logOpts' => $logOpts]);

        self::assertInstanceOf(SyslogHandler::class, $handler);

        self::assertSame(Logger::ALERT, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $identP = new ReflectionProperty($handler, 'ident');
        $identP->setAccessible(true);

        self::assertSame($ident, $identP->getValue($handler));

        $lo = new ReflectionProperty($handler, 'logopts');
        $lo->setAccessible(true);

        self::assertSame($logOpts, $lo->getValue($handler));

        $fa = new ReflectionProperty($handler, 'facility');
        $fa->setAccessible(true);

        self::assertSame($facility, $fa->getValue($handler));
    }
}
