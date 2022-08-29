<?php
/**
 * This file is part of the mimmi20/monolog-laminas-factory package.
 *
 * Copyright (c) 2021-2022, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20Test\LoggerFactory\Handler;

use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Mimmi20\LoggerFactory\Handler\DeduplicationHandlerFactory;
use Mimmi20\LoggerFactory\MonologFormatterPluginManager;
use Mimmi20\LoggerFactory\MonologHandlerPluginManager;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ChromePHPHandler;
use Monolog\Handler\DeduplicationHandler;
use Monolog\Logger;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

use function sprintf;

final class DeduplicationHandlerFactoryTest extends TestCase
{
    /** @throws Exception */
    public function testInvokeWithoutConfig(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new DeduplicationHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Options must be an Array');

        $factory($container, '');
    }

    /** @throws Exception */
    public function testInvokeWithEmptyConfig(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new DeduplicationHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No handler provided');

        $factory($container, '', []);
    }

    /** @throws Exception */
    public function testInvokeWithoutHandlerConfig(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new DeduplicationHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('HandlerConfig must be an Array');

        $factory($container, '', ['handler' => true]);
    }

    /** @throws Exception */
    public function testInvokeWithHandlerConfigWithoutType(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new DeduplicationHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Options must contain a type for the handler');

        $factory($container, '', ['handler' => []]);
    }

    /** @throws Exception */
    public function testInvokeWithHandlerConfigWithDisabledType(): void
    {
        $type = 'abc';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new DeduplicationHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No active handler specified');

        $factory($container, '', ['handler' => ['type' => $type, 'enabled' => false]]);
    }

    /** @throws Exception */
    public function testInvokeWithHandlerConfigWithLoaderError(): void
    {
        $type = 'abc';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologHandlerPluginManager::class)
            ->willThrowException(new ServiceNotCreatedException());

        $factory = new DeduplicationHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not load handler class %s', $type));

        $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true]]);
    }

    /** @throws Exception */
    public function testInvokeWithHandlerConfigWithLoaderError2(): void
    {
        $type = 'abc';

        $monologHandlerPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::once())
            ->method('get')
            ->with($type, [])
            ->willThrowException(new ServiceNotFoundException());

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologHandlerPluginManager::class)
            ->willReturn($monologHandlerPluginManager);

        $factory = new DeduplicationHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not load handler class %s', $type));

        $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true]]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvokeWithHandlerConfig(): void
    {
        $type           = 'abc';
        $formatterClass = $this->getMockBuilder(LineFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::once())
            ->method('getFormatter')
            ->willReturn($formatterClass);

        $monologHandlerPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::once())
            ->method('get')
            ->with($type, [])
            ->willReturn($handler2);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologHandlerPluginManager::class)
            ->willReturn($monologHandlerPluginManager);

        $factory = new DeduplicationHandlerFactory();

        $handler = $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true]]);

        self::assertInstanceOf(DeduplicationHandler::class, $handler);

        self::assertSame(Logger::DEBUG, $handler->getLevel());
        self::assertTrue($handler->getBubble());

        $handlerP = new ReflectionProperty($handler, 'handler');
        $handlerP->setAccessible(true);

        self::assertSame($handler2, $handlerP->getValue($handler));

        $bl = new ReflectionProperty($handler, 'bufferLimit');
        $bl->setAccessible(true);

        self::assertSame(0, $bl->getValue($handler));

        $fof = new ReflectionProperty($handler, 'flushOnOverflow');
        $fof->setAccessible(true);

        self::assertFalse($fof->getValue($handler));

        $dds = new ReflectionProperty($handler, 'deduplicationStore');
        $dds->setAccessible(true);

        self::assertIsString($dds->getValue($handler));

        $ddl = new ReflectionProperty($handler, 'deduplicationLevel');
        $ddl->setAccessible(true);

        self::assertSame(Logger::ERROR, $ddl->getValue($handler));

        $timeP = new ReflectionProperty($handler, 'time');
        $timeP->setAccessible(true);

        self::assertSame(60, $timeP->getValue($handler));

        self::assertSame($formatterClass, $handler->getFormatter());

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
    public function testInvokeWithHandlerConfig2(): void
    {
        $type               = 'abc';
        $deduplicationStore = 'test-link';
        $time               = 42;
        $formatterClass     = $this->getMockBuilder(LineFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::once())
            ->method('getFormatter')
            ->willReturn($formatterClass);

        $monologHandlerPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::once())
            ->method('get')
            ->with($type, [])
            ->willReturn($handler2);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologHandlerPluginManager::class)
            ->willReturn($monologHandlerPluginManager);

        $factory = new DeduplicationHandlerFactory();

        $handler = $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true], 'deduplicationStore' => $deduplicationStore, 'deduplicationLevel' => LogLevel::ALERT, 'time' => $time, 'bubble' => false]);

        self::assertInstanceOf(DeduplicationHandler::class, $handler);

        self::assertSame(Logger::DEBUG, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $handlerP = new ReflectionProperty($handler, 'handler');
        $handlerP->setAccessible(true);

        self::assertSame($handler2, $handlerP->getValue($handler));

        $bl = new ReflectionProperty($handler, 'bufferLimit');
        $bl->setAccessible(true);

        self::assertSame(0, $bl->getValue($handler));

        $fof = new ReflectionProperty($handler, 'flushOnOverflow');
        $fof->setAccessible(true);

        self::assertFalse($fof->getValue($handler));

        $dds = new ReflectionProperty($handler, 'deduplicationStore');
        $dds->setAccessible(true);

        self::assertSame($deduplicationStore, $dds->getValue($handler));

        $ddl = new ReflectionProperty($handler, 'deduplicationLevel');
        $ddl->setAccessible(true);

        self::assertSame(Logger::ALERT, $ddl->getValue($handler));

        $timeP = new ReflectionProperty($handler, 'time');
        $timeP->setAccessible(true);

        self::assertSame($time, $timeP->getValue($handler));

        self::assertSame($formatterClass, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');
        $proc->setAccessible(true);

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /** @throws Exception */
    public function testInvokeWithConfigAndBoolFormatter(): void
    {
        $type               = 'abc';
        $deduplicationStore = 'test-link';
        $time               = 42;
        $formatter          = true;

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::never())
            ->method('getFormatter');

        $monologHandlerPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::once())
            ->method('get')
            ->with($type, [])
            ->willReturn($handler2);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologHandlerPluginManager::class)
            ->willReturn($monologHandlerPluginManager);

        $factory = new DeduplicationHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class),
        );

        $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true], 'deduplicationStore' => $deduplicationStore, 'deduplicationLevel' => LogLevel::ALERT, 'time' => $time, 'bubble' => false, 'formatter' => $formatter]);
    }

    /** @throws Exception */
    public function testInvokeWithConfigAndBoolFormatter2(): void
    {
        $type               = 'abc';
        $deduplicationStore = 'test-link';
        $time               = 42;
        $formatter          = true;

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::never())
            ->method('getFormatter');

        $monologHandlerPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::once())
            ->method('get')
            ->with($type, ['formatter' => $formatter])
            ->willReturn($handler2);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologHandlerPluginManager::class)
            ->willReturn($monologHandlerPluginManager);

        $factory = new DeduplicationHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class),
        );

        $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true, 'options' => ['formatter' => $formatter]], 'deduplicationStore' => $deduplicationStore, 'deduplicationLevel' => LogLevel::ALERT, 'time' => $time, 'bubble' => false]);
    }

    /** @throws Exception */
    public function testInvokeWithConfigAndFormatter(): void
    {
        $type               = 'abc';
        $deduplicationStore = 'test-link';
        $time               = 42;
        $formatter          = $this->getMockBuilder(LineFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::never())
            ->method('getFormatter');

        $monologHandlerPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::once())
            ->method('get')
            ->with($type, [])
            ->willReturn($handler2);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive([MonologHandlerPluginManager::class], [MonologFormatterPluginManager::class])
            ->willReturnCallback(
                static function (string $var) use ($monologHandlerPluginManager): AbstractPluginManager {
                    if (MonologHandlerPluginManager::class === $var) {
                        return $monologHandlerPluginManager;
                    }

                    throw new ServiceNotFoundException();
                },
            );

        $factory = new DeduplicationHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologFormatterPluginManager::class),
        );

        $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true], 'deduplicationStore' => $deduplicationStore, 'deduplicationLevel' => LogLevel::ALERT, 'time' => $time, 'bubble' => false, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvokeWithConfigAndFormatter2(): void
    {
        $type               = 'abc';
        $deduplicationStore = 'test-link';
        $time               = 42;
        $formatter          = $this->getMockBuilder(LineFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler2->expects(self::once())
            ->method('setFormatter')
            ->with($formatter);
        $handler2->expects(self::once())
            ->method('getFormatter')
            ->willReturn($formatter);

        $monologFormatterPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologFormatterPluginManager->expects(self::never())
            ->method('has');
        $monologFormatterPluginManager->expects(self::never())
            ->method('get');

        $monologHandlerPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::once())
            ->method('get')
            ->with($type, [])
            ->willReturn($handler2);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive([MonologHandlerPluginManager::class], [MonologFormatterPluginManager::class])
            ->willReturnOnConsecutiveCalls($monologHandlerPluginManager, $monologFormatterPluginManager);

        $factory = new DeduplicationHandlerFactory();

        $handler = $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true], 'deduplicationStore' => $deduplicationStore, 'deduplicationLevel' => LogLevel::ALERT, 'time' => $time, 'bubble' => false, 'formatter' => $formatter]);

        self::assertInstanceOf(DeduplicationHandler::class, $handler);

        self::assertSame(Logger::DEBUG, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $handlerP = new ReflectionProperty($handler, 'handler');
        $handlerP->setAccessible(true);

        self::assertSame($handler2, $handlerP->getValue($handler));

        $bl = new ReflectionProperty($handler, 'bufferLimit');
        $bl->setAccessible(true);

        self::assertSame(0, $bl->getValue($handler));

        $fof = new ReflectionProperty($handler, 'flushOnOverflow');
        $fof->setAccessible(true);

        self::assertFalse($fof->getValue($handler));

        $dds = new ReflectionProperty($handler, 'deduplicationStore');
        $dds->setAccessible(true);

        self::assertSame($deduplicationStore, $dds->getValue($handler));

        $ddl = new ReflectionProperty($handler, 'deduplicationLevel');
        $ddl->setAccessible(true);

        self::assertSame(Logger::ALERT, $ddl->getValue($handler));

        $timeP = new ReflectionProperty($handler, 'time');
        $timeP->setAccessible(true);

        self::assertSame($time, $timeP->getValue($handler));

        self::assertSame($formatter, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');
        $proc->setAccessible(true);

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /** @throws Exception */
    public function testInvokeWithConfigAndFormatter3(): void
    {
        $type               = 'abc';
        $deduplicationStore = 'test-link';
        $time               = 42;
        $formatter          = $this->getMockBuilder(LineFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::never())
            ->method('getFormatter');

        $monologHandlerPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::once())
            ->method('get')
            ->with($type, ['formatter' => $formatter])
            ->willReturn($handler2);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive([MonologHandlerPluginManager::class], [MonologFormatterPluginManager::class])
            ->willReturnCallback(
                static function (string $var) use ($monologHandlerPluginManager): AbstractPluginManager {
                    if (MonologHandlerPluginManager::class === $var) {
                        return $monologHandlerPluginManager;
                    }

                    throw new ServiceNotFoundException();
                },
            );

        $factory = new DeduplicationHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologFormatterPluginManager::class),
        );

        $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true, 'options' => ['formatter' => $formatter]], 'deduplicationStore' => $deduplicationStore, 'deduplicationLevel' => LogLevel::ALERT, 'time' => $time, 'bubble' => false]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     */
    public function testInvokeWithConfigAndFormatter4(): void
    {
        $type               = 'abc';
        $deduplicationStore = 'test-link';
        $time               = 42;
        $formatter          = $this->getMockBuilder(LineFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler2->expects(self::once())
            ->method('setFormatter')
            ->with($formatter);
        $handler2->expects(self::once())
            ->method('getFormatter')
            ->willReturn($formatter);

        $monologFormatterPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologFormatterPluginManager->expects(self::never())
            ->method('has');
        $monologFormatterPluginManager->expects(self::never())
            ->method('get');

        $monologHandlerPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::once())
            ->method('get')
            ->with($type, ['formatter' => $formatter])
            ->willReturn($handler2);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive([MonologHandlerPluginManager::class], [MonologFormatterPluginManager::class])
            ->willReturnOnConsecutiveCalls($monologHandlerPluginManager, $monologFormatterPluginManager);

        $factory = new DeduplicationHandlerFactory();

        $handler = $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true, 'options' => ['formatter' => $formatter]], 'deduplicationStore' => $deduplicationStore, 'deduplicationLevel' => LogLevel::ALERT, 'time' => $time, 'bubble' => false]);

        self::assertInstanceOf(DeduplicationHandler::class, $handler);

        self::assertSame(Logger::DEBUG, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $handlerP = new ReflectionProperty($handler, 'handler');
        $handlerP->setAccessible(true);

        self::assertSame($handler2, $handlerP->getValue($handler));

        $bl = new ReflectionProperty($handler, 'bufferLimit');
        $bl->setAccessible(true);

        self::assertSame(0, $bl->getValue($handler));

        $fof = new ReflectionProperty($handler, 'flushOnOverflow');
        $fof->setAccessible(true);

        self::assertFalse($fof->getValue($handler));

        $dds = new ReflectionProperty($handler, 'deduplicationStore');
        $dds->setAccessible(true);

        self::assertSame($deduplicationStore, $dds->getValue($handler));

        $ddl = new ReflectionProperty($handler, 'deduplicationLevel');
        $ddl->setAccessible(true);

        self::assertSame(Logger::ALERT, $ddl->getValue($handler));

        $timeP = new ReflectionProperty($handler, 'time');
        $timeP->setAccessible(true);

        self::assertSame($time, $timeP->getValue($handler));

        self::assertSame($formatter, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');
        $proc->setAccessible(true);

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /** @throws Exception */
    public function testInvokeWithConfigAndBoolProcessors(): void
    {
        $type               = 'abc';
        $deduplicationStore = 'test-link';
        $time               = 42;
        $processors         = true;

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::never())
            ->method('getFormatter');

        $monologHandlerPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::once())
            ->method('get')
            ->with($type, [])
            ->willReturn($handler2);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologHandlerPluginManager::class)
            ->willReturn($monologHandlerPluginManager);

        $factory = new DeduplicationHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Processors must be an Array');

        $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true], 'deduplicationStore' => $deduplicationStore, 'deduplicationLevel' => LogLevel::ALERT, 'time' => $time, 'bubble' => false, 'processors' => $processors]);
    }

    /** @throws Exception */
    public function testInvokeWithConfigAndBoolProcessors2(): void
    {
        $type               = 'abc';
        $deduplicationStore = 'test-link';
        $time               = 42;
        $processors         = true;

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::never())
            ->method('getFormatter');

        $monologHandlerPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::once())
            ->method('get')
            ->with($type, ['processors' => $processors])
            ->willReturn($handler2);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologHandlerPluginManager::class)
            ->willReturn($monologHandlerPluginManager);

        $factory = new DeduplicationHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Processors must be an Array');

        $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true, 'options' => ['processors' => $processors]], 'deduplicationStore' => $deduplicationStore, 'deduplicationLevel' => LogLevel::ALERT, 'time' => $time, 'bubble' => false]);
    }
}
