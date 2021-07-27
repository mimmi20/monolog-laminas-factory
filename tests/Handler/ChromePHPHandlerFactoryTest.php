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
use Mimmi20\LoggerFactory\Handler\ChromePHPHandlerFactory;
use Monolog\Handler\ChromePHPHandler;
use Monolog\Logger;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

final class ChromePHPHandlerFactoryTest extends TestCase
{
    /**
     * @throws Exception
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

        $factory = new ChromePHPHandlerFactory();

        $handler = $factory($container, '');

        self::assertInstanceOf(ChromePHPHandler::class, $handler);

        self::assertSame(Logger::DEBUG, $handler->getLevel());
        self::assertTrue($handler->getBubble());
    }

    /**
     * @throws Exception
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

        $factory = new ChromePHPHandlerFactory();

        $handler = $factory($container, '', []);

        self::assertInstanceOf(ChromePHPHandler::class, $handler);

        self::assertSame(Logger::DEBUG, $handler->getLevel());
        self::assertTrue($handler->getBubble());
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testInvoceWithConfig(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new ChromePHPHandlerFactory();

        $handler = $factory($container, '', ['level' => LogLevel::ALERT, 'bubble' => false]);

        self::assertInstanceOf(ChromePHPHandler::class, $handler);

        self::assertSame(Logger::ALERT, $handler->getLevel());
        self::assertFalse($handler->getBubble());
    }
}
