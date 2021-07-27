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

use AMQPExchange;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Mimmi20\LoggerFactory\Handler\AmqpHandlerFactory;
use Monolog\Handler\AmqpHandler;
use Monolog\Logger;
use PhpAmqpLib\Channel\AMQPChannel;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

use function class_exists;
use function sprintf;

final class AmqpHandlerFactoryTest extends TestCase
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

        $factory = new AmqpHandlerFactory();

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

        $factory = new AmqpHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No Service name provided for the required exchange class');

        $factory($container, '', []);
    }

    /**
     * @throws Exception
     */
    public function testInvoceWithWrongExchange(): void
    {
        $exchange = true;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new AmqpHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No Service name provided for the required exchange class');

        $factory($container, '', ['exchange' => $exchange]);
    }

    /**
     * @throws Exception
     */
    public function testInvoceWithWrongExchange2(): void
    {
        $exchange = 'test';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($exchange)
            ->willThrowException(new ServiceNotFoundException());

        $factory = new AmqpHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Could not load exchange class');

        $factory($container, '', ['exchange' => $exchange]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvoceWithConfig(): void
    {
        if (!class_exists(AMQPExchange::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', AMQPExchange::class));
        }

        $exchange      = 'test';
        $exchangeClass = $this->getMockBuilder(AMQPExchange::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($exchange)
            ->willReturn($exchangeClass);

        $factory = new AmqpHandlerFactory();

        $handler = $factory($container, '', ['exchange' => $exchange]);

        self::assertInstanceOf(AmqpHandler::class, $handler);

        self::assertSame(Logger::DEBUG, $handler->getLevel());
        self::assertTrue($handler->getBubble());

        $ec = new ReflectionProperty($handler, 'exchange');
        $ec->setAccessible(true);

        self::assertSame($exchangeClass, $ec->getValue($handler));

        $ecn = new ReflectionProperty($handler, 'exchangeName');
        $ecn->setAccessible(true);

        self::assertNull($ecn->getValue($handler));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvoceWithConfig2(): void
    {
        if (!class_exists(AMQPExchange::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', AMQPExchange::class));
        }

        $exchange      = 'test';
        $exchangeClass = $this->getMockBuilder(AMQPExchange::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($exchange)
            ->willReturn($exchangeClass);

        $factory = new AmqpHandlerFactory();

        $handler = $factory($container, '', ['exchange' => $exchange, 'level' => LogLevel::ALERT, 'bubble' => false]);

        self::assertInstanceOf(AmqpHandler::class, $handler);

        self::assertSame(Logger::ALERT, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $ec = new ReflectionProperty($handler, 'exchange');
        $ec->setAccessible(true);

        self::assertSame($exchangeClass, $ec->getValue($handler));

        $ecn = new ReflectionProperty($handler, 'exchangeName');
        $ecn->setAccessible(true);

        self::assertNull($ecn->getValue($handler));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvoceWithConfig3(): void
    {
        if (!class_exists(AMQPExchange::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', AMQPExchange::class));
        }

        $exchangeClass = $this->getMockBuilder(AMQPExchange::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new AmqpHandlerFactory();

        $handler = $factory($container, '', ['exchange' => $exchangeClass]);

        self::assertInstanceOf(AmqpHandler::class, $handler);

        self::assertSame(Logger::DEBUG, $handler->getLevel());
        self::assertTrue($handler->getBubble());

        $ec = new ReflectionProperty($handler, 'exchange');
        $ec->setAccessible(true);

        self::assertSame($exchangeClass, $ec->getValue($handler));

        $ecn = new ReflectionProperty($handler, 'exchangeName');
        $ecn->setAccessible(true);

        self::assertNull($ecn->getValue($handler));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvoceWithConfig4(): void
    {
        if (!class_exists(AMQPExchange::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', AMQPExchange::class));
        }

        $exchangeClass = $this->getMockBuilder(AMQPExchange::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new AmqpHandlerFactory();

        $handler = $factory($container, '', ['exchange' => $exchangeClass, 'level' => LogLevel::ALERT, 'bubble' => false]);

        self::assertInstanceOf(AmqpHandler::class, $handler);

        self::assertSame(Logger::ALERT, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $ec = new ReflectionProperty($handler, 'exchange');
        $ec->setAccessible(true);

        self::assertSame($exchangeClass, $ec->getValue($handler));

        $ecn = new ReflectionProperty($handler, 'exchangeName');
        $ecn->setAccessible(true);

        self::assertNull($ecn->getValue($handler));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvoceWithConfig5(): void
    {
        if (!class_exists(AMQPChannel::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', AMQPChannel::class));
        }

        $exchange      = 'test';
        $exchangeClass = $this->getMockBuilder(AMQPChannel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($exchange)
            ->willReturn($exchangeClass);

        $factory = new AmqpHandlerFactory();

        $handler = $factory($container, '', ['exchange' => $exchange]);

        self::assertInstanceOf(AmqpHandler::class, $handler);

        self::assertSame(Logger::DEBUG, $handler->getLevel());
        self::assertTrue($handler->getBubble());

        $ec = new ReflectionProperty($handler, 'exchange');
        $ec->setAccessible(true);

        self::assertSame($exchangeClass, $ec->getValue($handler));

        $ecn = new ReflectionProperty($handler, 'exchangeName');
        $ecn->setAccessible(true);

        self::assertSame('log', $ecn->getValue($handler));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvoceWithConfig6(): void
    {
        if (!class_exists(AMQPChannel::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', AMQPChannel::class));
        }

        $exchange      = 'test';
        $exchangeName  = 'exchange-name-test';
        $exchangeClass = $this->getMockBuilder(AMQPChannel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($exchange)
            ->willReturn($exchangeClass);

        $factory = new AmqpHandlerFactory();

        $handler = $factory($container, '', ['exchange' => $exchange, 'exchangeName' => $exchangeName, 'level' => LogLevel::ALERT, 'bubble' => false]);

        self::assertInstanceOf(AmqpHandler::class, $handler);

        self::assertSame(Logger::ALERT, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $ec = new ReflectionProperty($handler, 'exchange');
        $ec->setAccessible(true);

        self::assertSame($exchangeClass, $ec->getValue($handler));

        $ecn = new ReflectionProperty($handler, 'exchangeName');
        $ecn->setAccessible(true);

        self::assertSame($exchangeName, $ecn->getValue($handler));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvoceWithConfig7(): void
    {
        if (!class_exists(AMQPChannel::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', AMQPChannel::class));
        }

        $exchangeClass = $this->getMockBuilder(AMQPChannel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new AmqpHandlerFactory();

        $handler = $factory($container, '', ['exchange' => $exchangeClass]);

        self::assertInstanceOf(AmqpHandler::class, $handler);

        self::assertSame(Logger::DEBUG, $handler->getLevel());
        self::assertTrue($handler->getBubble());

        $ec = new ReflectionProperty($handler, 'exchange');
        $ec->setAccessible(true);

        self::assertSame($exchangeClass, $ec->getValue($handler));

        $ecn = new ReflectionProperty($handler, 'exchangeName');
        $ecn->setAccessible(true);

        self::assertSame('log', $ecn->getValue($handler));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvoceWithConfig8(): void
    {
        if (!class_exists(AMQPChannel::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', AMQPChannel::class));
        }

        $exchangeName  = 'exchange-name-test';
        $exchangeClass = $this->getMockBuilder(AMQPChannel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new AmqpHandlerFactory();

        $handler = $factory($container, '', ['exchange' => $exchangeClass, 'exchangeName' => $exchangeName, 'level' => LogLevel::ALERT, 'bubble' => false]);

        self::assertInstanceOf(AmqpHandler::class, $handler);

        self::assertSame(Logger::ALERT, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $ec = new ReflectionProperty($handler, 'exchange');
        $ec->setAccessible(true);

        self::assertSame($exchangeClass, $ec->getValue($handler));

        $ecn = new ReflectionProperty($handler, 'exchangeName');
        $ecn->setAccessible(true);

        self::assertSame($exchangeName, $ecn->getValue($handler));
    }
}
