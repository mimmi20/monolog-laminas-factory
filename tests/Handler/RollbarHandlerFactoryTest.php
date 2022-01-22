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
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Mimmi20\LoggerFactory\Handler\RollbarHandlerFactory;
use Mimmi20\LoggerFactory\MonologFormatterPluginManager;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RollbarHandler;
use Monolog\Logger;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;
use Rollbar\Config;
use Rollbar\RollbarLogger;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

use function assert;
use function class_exists;
use function sprintf;

/**
 * @requires PHP < 8.1
 */
final class RollbarHandlerFactoryTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testInvoceWithoutConfig(): void
    {
        if (!class_exists(Config::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Config::class));
        }

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new RollbarHandlerFactory();

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
        if (!class_exists(Config::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Config::class));
        }

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new RollbarHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No access token provided');

        $factory($container, '', []);
    }

    /**
     * @throws Exception
     */
    public function testInvoceWithTooShortToken(): void
    {
        if (!class_exists(Config::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Config::class));
        }

        $token = 'token';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new RollbarHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not create service %s', RollbarLogger::class));

        $factory($container, '', ['access_token' => $token]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvoceWithConfig(): void
    {
        if (!class_exists(Config::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Config::class));
        }

        $token = 'tokentokentokentokentokentokenab';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new RollbarHandlerFactory();

        $handler = $factory($container, '', ['access_token' => $token]);

        self::assertInstanceOf(RollbarHandler::class, $handler);

        self::assertSame(Logger::DEBUG, $handler->getLevel());
        self::assertTrue($handler->getBubble());

        $rollbarloggerP = new ReflectionProperty($handler, 'rollbarLogger');
        $rollbarloggerP->setAccessible(true);

        $rollbarlogger = $rollbarloggerP->getValue($handler);
        assert($rollbarlogger instanceof RollbarLogger);

        $rollbarConfigP = new ReflectionProperty($rollbarlogger, 'config');
        $rollbarConfigP->setAccessible(true);

        $rollbarConfig = $rollbarConfigP->getValue($rollbarlogger);
        assert($rollbarConfig instanceof Config);

        self::assertSame($token, $rollbarConfig->getAccessToken());
        self::assertTrue($rollbarConfig->enabled());
        self::assertTrue($rollbarConfig->transmitting());
        self::assertTrue($rollbarConfig->loggingPayload());
        self::assertSame(Config::VERBOSE_NONE, $rollbarConfig->verbose());
        self::assertSame('production', $rollbarConfig->getDataBuilder()->getEnvironment());

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
    public function testInvoceWithConfig2(): void
    {
        if (!class_exists(Config::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Config::class));
        }

        $token       = 'tokentokentokentokentokentokenab';
        $verbose     = LogLevel::ALERT;
        $environment = 'test';
        $level       = LogLevel::ERROR;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new RollbarHandlerFactory();

        $handler = $factory($container, '', ['access_token' => $token, 'enabled' => false, 'transmit' => false, 'log_payload' => false, 'verbose' => $verbose, 'environment' => $environment, 'bubble' => false, 'level' => $level]);

        self::assertInstanceOf(RollbarHandler::class, $handler);

        self::assertSame(Logger::ERROR, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $rollbarloggerP = new ReflectionProperty($handler, 'rollbarLogger');
        $rollbarloggerP->setAccessible(true);

        $rollbarlogger = $rollbarloggerP->getValue($handler);
        assert($rollbarlogger instanceof RollbarLogger);

        $rollbarConfigP = new ReflectionProperty($rollbarlogger, 'config');
        $rollbarConfigP->setAccessible(true);

        $rollbarConfig = $rollbarConfigP->getValue($rollbarlogger);
        assert($rollbarConfig instanceof Config);

        self::assertSame($token, $rollbarConfig->getAccessToken());
        self::assertFalse($rollbarConfig->enabled());
        self::assertFalse($rollbarConfig->transmitting());
        self::assertFalse($rollbarConfig->loggingPayload());
        self::assertSame($verbose, $rollbarConfig->verbose());
        self::assertSame($environment, $rollbarConfig->getDataBuilder()->getEnvironment());

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
        if (!class_exists(Config::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Config::class));
        }

        $token       = 'tokentokentokentokentokentokenab';
        $verbose     = LogLevel::ALERT;
        $environment = 'test';
        $level       = LogLevel::ERROR;
        $formatter   = true;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new RollbarHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class)
        );

        $factory($container, '', ['access_token' => $token, 'enabled' => false, 'transmit' => false, 'log_payload' => false, 'verbose' => $verbose, 'environment' => $environment, 'bubble' => false, 'level' => $level, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     */
    public function testInvoceWithConfigAndFormatter(): void
    {
        if (!class_exists(Config::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Config::class));
        }

        $token       = 'tokentokentokentokentokentokenab';
        $verbose     = LogLevel::ALERT;
        $environment = 'test';
        $level       = LogLevel::ERROR;
        $formatter   = $this->getMockBuilder(LineFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologFormatterPluginManager::class)
            ->willThrowException(new ServiceNotFoundException());

        $factory = new RollbarHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologFormatterPluginManager::class)
        );

        $factory($container, '', ['access_token' => $token, 'enabled' => false, 'transmit' => false, 'log_payload' => false, 'verbose' => $verbose, 'environment' => $environment, 'bubble' => false, 'level' => $level, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvoceWithConfigAndFormatter2(): void
    {
        if (!class_exists(Config::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Config::class));
        }

        $token       = 'tokentokentokentokentokentokenab';
        $verbose     = LogLevel::ALERT;
        $environment = 'test';
        $level       = LogLevel::ERROR;
        $formatter   = $this->getMockBuilder(LineFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $monologFormatterPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologFormatterPluginManager->expects(self::never())
            ->method('has');
        $monologFormatterPluginManager->expects(self::never())
            ->method('get');

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologFormatterPluginManager::class)
            ->willReturn($monologFormatterPluginManager);

        $factory = new RollbarHandlerFactory();

        $handler = $factory($container, '', ['access_token' => $token, 'enabled' => false, 'transmit' => false, 'log_payload' => false, 'verbose' => $verbose, 'environment' => $environment, 'bubble' => false, 'level' => $level, 'formatter' => $formatter]);

        self::assertInstanceOf(RollbarHandler::class, $handler);

        self::assertSame(Logger::ERROR, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $rollbarloggerP = new ReflectionProperty($handler, 'rollbarLogger');
        $rollbarloggerP->setAccessible(true);

        $rollbarlogger = $rollbarloggerP->getValue($handler);
        assert($rollbarlogger instanceof RollbarLogger);

        $rollbarConfigP = new ReflectionProperty($rollbarlogger, 'config');
        $rollbarConfigP->setAccessible(true);

        $rollbarConfig = $rollbarConfigP->getValue($rollbarlogger);
        assert($rollbarConfig instanceof Config);

        self::assertSame($token, $rollbarConfig->getAccessToken());
        self::assertFalse($rollbarConfig->enabled());
        self::assertFalse($rollbarConfig->transmitting());
        self::assertFalse($rollbarConfig->loggingPayload());
        self::assertSame($verbose, $rollbarConfig->verbose());
        self::assertSame($environment, $rollbarConfig->getDataBuilder()->getEnvironment());

        self::assertSame($formatter, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');
        $proc->setAccessible(true);

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     */
    public function testInvoceWithConfigAndBoolProcessors(): void
    {
        if (!class_exists(Config::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Config::class));
        }

        $token       = 'tokentokentokentokentokentokenab';
        $verbose     = LogLevel::ALERT;
        $environment = 'test';
        $level       = LogLevel::ERROR;
        $processors  = true;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new RollbarHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Processors must be an Array');

        $factory($container, '', ['access_token' => $token, 'enabled' => false, 'transmit' => false, 'log_payload' => false, 'verbose' => $verbose, 'environment' => $environment, 'bubble' => false, 'level' => $level, 'processors' => $processors]);
    }
}
