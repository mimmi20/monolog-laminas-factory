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
use Mimmi20\LoggerFactory\Handler\WhatFailureGroupHandlerFactory;
use Mimmi20\LoggerFactory\MonologHandlerPluginManager;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ChromePHPHandler;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\GelfHandler;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\WhatFailureGroupHandler;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionException;
use ReflectionProperty;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

use function sprintf;

final class WhatFailureGroupHandlerFactoryTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testInvokeWithoutConfig(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new WhatFailureGroupHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Options must be an Array');

        $factory($container, '');
    }

    /**
     * @throws Exception
     */
    public function testInvokeWithEmptyConfig(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new WhatFailureGroupHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No Service names provided for the required handler classes');

        $factory($container, '', []);
    }

    /**
     * @throws Exception
     */
    public function testInvokeWithEmptyConfig2(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new WhatFailureGroupHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No Service names provided for the required handler classes');

        $factory($container, '', ['handlers' => true]);
    }

    /**
     * @throws Exception
     */
    public function testInvokeWithoutHandlers(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new WhatFailureGroupHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No active handlers specified');

        $factory($container, '', ['handlers' => []]);
    }

    /**
     * @throws Exception
     */
    public function testInvokeWithStringHandlers(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new WhatFailureGroupHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('HandlerConfig must be an Array');

        $factory($container, '', ['handlers' => ['test']]);
    }

    /**
     * @throws Exception
     */
    public function testInvokeWithHandlerWithoutType(): void
    {
        $handlers = [[]];

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new WhatFailureGroupHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Options must contain a type for the handler');

        $factory($container, '', ['handlers' => $handlers]);
    }

    /**
     * @throws Exception
     */
    public function testInvokeWithHandlerWithType(): void
    {
        $handlers = [
            [
                'type' => FingersCrossedHandler::class,
                'enabled' => false,
            ],
            [
                'type' => FirePHPHandler::class,
                'enabled' => true,
            ],
            [
                'type' => ChromePHPHandler::class,
            ],
            [
                'type' => GelfHandler::class,
            ],
        ];

        $handler1 = $this->getMockBuilder(FirePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler1->expects(self::never())
            ->method('setFormatter');
        $handler1->expects(self::never())
            ->method('getFormatter');

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
        $monologHandlerPluginManager->expects(self::exactly(3))
            ->method('get')
            ->withConsecutive([FirePHPHandler::class, []], [ChromePHPHandler::class, []], [GelfHandler::class, []])
            ->willReturnCallback(
                static function (string $with) use ($handler1, $handler2): HandlerInterface {
                    if (FirePHPHandler::class === $with) {
                        return $handler1;
                    }

                    if (ChromePHPHandler::class === $with) {
                        return $handler2;
                    }

                    throw new ServiceNotFoundException();
                }
            );

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(3))
            ->method('get')
            ->with(MonologHandlerPluginManager::class)
            ->willReturn($monologHandlerPluginManager);

        $factory = new WhatFailureGroupHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not load handler class %s', GelfHandler::class));

        $factory($container, '', ['handlers' => $handlers]);
    }

    /**
     * @throws Exception
     */
    public function testInvokeWithHandlerWithType2(): void
    {
        $handlers = [
            [
                'type' => FingersCrossedHandler::class,
                'enabled' => false,
            ],
            [
                'type' => FirePHPHandler::class,
                'enabled' => true,
            ],
            [
                'type' => ChromePHPHandler::class,
            ],
            [
                'type' => GelfHandler::class,
            ],
        ];

        $handler1 = $this->getMockBuilder(FirePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler1->expects(self::never())
            ->method('setFormatter');
        $handler1->expects(self::never())
            ->method('getFormatter');

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
        $monologHandlerPluginManager->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive([FirePHPHandler::class, []], [ChromePHPHandler::class, []])
            ->willReturnOnConsecutiveCalls($handler1, $handler2);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(3))
            ->method('get')
            ->with(MonologHandlerPluginManager::class)
            ->willReturnCallback(
                static function () use ($monologHandlerPluginManager): AbstractPluginManager {
                    static $number = 0;
                    ++$number;

                    if (3 > $number) {
                        return $monologHandlerPluginManager;
                    }

                    throw new ServiceNotFoundException();
                }
            );

        $factory = new WhatFailureGroupHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not load handler class %s', GelfHandler::class));

        $factory($container, '', ['handlers' => $handlers]);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function testInvokeWithHandlerWithType3(): void
    {
        $handlers = [
            [
                'type' => FingersCrossedHandler::class,
                'enabled' => false,
            ],
            [
                'type' => FirePHPHandler::class,
                'enabled' => true,
            ],
            [
                'type' => ChromePHPHandler::class,
            ],
            [
                'type' => GelfHandler::class,
            ],
        ];

        $handler1 = $this->getMockBuilder(FirePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler1->expects(self::never())
            ->method('setFormatter');
        $handler1->expects(self::never())
            ->method('getFormatter');

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::never())
            ->method('getFormatter');

        $handler3 = $this->getMockBuilder(GelfHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler3->expects(self::never())
            ->method('setFormatter');
        $handler3->expects(self::never())
            ->method('getFormatter');

        $monologHandlerPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::exactly(3))
            ->method('get')
            ->withConsecutive([FirePHPHandler::class, []], [ChromePHPHandler::class, []], [GelfHandler::class, []])
            ->willReturnOnConsecutiveCalls($handler1, $handler2, $handler3);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(3))
            ->method('get')
            ->with(MonologHandlerPluginManager::class)
            ->willReturn($monologHandlerPluginManager);

        $factory = new WhatFailureGroupHandlerFactory();

        $handler = $factory($container, '', ['handlers' => $handlers]);

        self::assertInstanceOf(WhatFailureGroupHandler::class, $handler);

        $fp = new ReflectionProperty($handler, 'handlers');
        $fp->setAccessible(true);

        $handlerClasses = $fp->getValue($handler);

        self::assertIsArray($handlerClasses);
        self::assertCount(3, $handlerClasses);
        self::assertSame($handler1, $handlerClasses[0]);
        self::assertSame($handler2, $handlerClasses[1]);
        self::assertSame($handler3, $handlerClasses[2]);

        $bubble = new ReflectionProperty($handler, 'bubble');
        $bubble->setAccessible(true);

        self::assertTrue($bubble->getValue($handler));

        $proc = new ReflectionProperty($handler, 'processors');
        $proc->setAccessible(true);

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function testInvokeWithHandlerWithType4(): void
    {
        $handlers = [
            [
                'type' => FingersCrossedHandler::class,
                'enabled' => false,
            ],
            [
                'type' => FirePHPHandler::class,
                'enabled' => true,
            ],
            [
                'type' => ChromePHPHandler::class,
            ],
            [
                'type' => GelfHandler::class,
            ],
        ];

        $handler1 = $this->getMockBuilder(FirePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler1->expects(self::never())
            ->method('setFormatter');
        $handler1->expects(self::never())
            ->method('getFormatter');

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::never())
            ->method('getFormatter');

        $handler3 = $this->getMockBuilder(GelfHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler3->expects(self::never())
            ->method('setFormatter');
        $handler3->expects(self::never())
            ->method('getFormatter');

        $monologHandlerPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::exactly(3))
            ->method('get')
            ->withConsecutive([FirePHPHandler::class, []], [ChromePHPHandler::class, []], [GelfHandler::class, []])
            ->willReturnOnConsecutiveCalls($handler1, $handler2, $handler3);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(3))
            ->method('get')
            ->with(MonologHandlerPluginManager::class)
            ->willReturn($monologHandlerPluginManager);

        $factory = new WhatFailureGroupHandlerFactory();

        $handler = $factory($container, '', ['handlers' => $handlers, 'bubble' => false]);

        self::assertInstanceOf(WhatFailureGroupHandler::class, $handler);

        $fp = new ReflectionProperty($handler, 'handlers');
        $fp->setAccessible(true);

        $handlerClasses = $fp->getValue($handler);

        self::assertIsArray($handlerClasses);
        self::assertCount(3, $handlerClasses);
        self::assertSame($handler1, $handlerClasses[0]);
        self::assertSame($handler2, $handlerClasses[1]);
        self::assertSame($handler3, $handlerClasses[2]);

        $bubble = new ReflectionProperty($handler, 'bubble');
        $bubble->setAccessible(true);

        self::assertFalse($bubble->getValue($handler));

        $proc = new ReflectionProperty($handler, 'processors');
        $proc->setAccessible(true);

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function testInvokeWithConfigAndBoolFormatter(): void
    {
        $formatter = true;
        $handlers  = [
            [
                'type' => FingersCrossedHandler::class,
                'enabled' => false,
            ],
            [
                'type' => FirePHPHandler::class,
                'enabled' => true,
            ],
            [
                'type' => ChromePHPHandler::class,
            ],
            [
                'type' => GelfHandler::class,
            ],
        ];

        $handler1 = $this->getMockBuilder(FirePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler1->expects(self::never())
            ->method('setFormatter');
        $handler1->expects(self::never())
            ->method('getFormatter');

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::never())
            ->method('getFormatter');

        $handler3 = $this->getMockBuilder(GelfHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler3->expects(self::never())
            ->method('setFormatter');
        $handler3->expects(self::never())
            ->method('getFormatter');

        $monologHandlerPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::exactly(3))
            ->method('get')
            ->withConsecutive([FirePHPHandler::class, []], [ChromePHPHandler::class, []], [GelfHandler::class, []])
            ->willReturnOnConsecutiveCalls($handler1, $handler2, $handler3);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(3))
            ->method('get')
            ->with(MonologHandlerPluginManager::class)
            ->willReturn($monologHandlerPluginManager);

        $factory = new WhatFailureGroupHandlerFactory();

        $handler = $factory($container, '', ['handlers' => $handlers, 'bubble' => false, 'formatter' => $formatter]);

        self::assertInstanceOf(WhatFailureGroupHandler::class, $handler);

        $fp = new ReflectionProperty($handler, 'handlers');
        $fp->setAccessible(true);

        $handlerClasses = $fp->getValue($handler);

        self::assertIsArray($handlerClasses);
        self::assertCount(3, $handlerClasses);
        self::assertSame($handler1, $handlerClasses[0]);
        self::assertSame($handler2, $handlerClasses[1]);
        self::assertSame($handler3, $handlerClasses[2]);

        $bubble = new ReflectionProperty($handler, 'bubble');
        $bubble->setAccessible(true);

        self::assertFalse($bubble->getValue($handler));

        $proc = new ReflectionProperty($handler, 'processors');
        $proc->setAccessible(true);

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function testInvokeWithConfigAndFormatter(): void
    {
        $formatter = $this->getMockBuilder(LineFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handlers  = [
            [
                'type' => FingersCrossedHandler::class,
                'enabled' => false,
            ],
            [
                'type' => FirePHPHandler::class,
                'enabled' => true,
            ],
            [
                'type' => ChromePHPHandler::class,
            ],
            [
                'type' => GelfHandler::class,
            ],
        ];

        $handler1 = $this->getMockBuilder(FirePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler1->expects(self::never())
            ->method('setFormatter');
        $handler1->expects(self::never())
            ->method('getFormatter');

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::never())
            ->method('getFormatter');

        $handler3 = $this->getMockBuilder(GelfHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler3->expects(self::never())
            ->method('setFormatter');
        $handler3->expects(self::never())
            ->method('getFormatter');

        $monologHandlerPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::exactly(3))
            ->method('get')
            ->withConsecutive([FirePHPHandler::class, []], [ChromePHPHandler::class, []], [GelfHandler::class, []])
            ->willReturnOnConsecutiveCalls($handler1, $handler2, $handler3);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(3))
            ->method('get')
            ->with(MonologHandlerPluginManager::class)
            ->willReturn($monologHandlerPluginManager);

        $factory = new WhatFailureGroupHandlerFactory();

        $handler = $factory($container, '', ['handlers' => $handlers, 'bubble' => false, 'formatter' => $formatter]);

        self::assertInstanceOf(WhatFailureGroupHandler::class, $handler);

        $fp = new ReflectionProperty($handler, 'handlers');
        $fp->setAccessible(true);

        $handlerClasses = $fp->getValue($handler);

        self::assertIsArray($handlerClasses);
        self::assertCount(3, $handlerClasses);
        self::assertSame($handler1, $handlerClasses[0]);
        self::assertSame($handler2, $handlerClasses[1]);
        self::assertSame($handler3, $handlerClasses[2]);

        $bubble = new ReflectionProperty($handler, 'bubble');
        $bubble->setAccessible(true);

        self::assertFalse($bubble->getValue($handler));

        $proc = new ReflectionProperty($handler, 'processors');
        $proc->setAccessible(true);

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function testInvokeWithConfigAndFormatter2(): void
    {
        $formatter = $this->getMockBuilder(LineFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handlers  = [
            [
                'type' => FingersCrossedHandler::class,
                'enabled' => false,
            ],
            [
                'type' => FirePHPHandler::class,
                'enabled' => true,
            ],
            [
                'type' => ChromePHPHandler::class,
            ],
            [
                'type' => GelfHandler::class,
            ],
        ];

        $handler1 = $this->getMockBuilder(FirePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler1->expects(self::never())
            ->method('setFormatter');
        $handler1->expects(self::never())
            ->method('getFormatter');

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::never())
            ->method('getFormatter');

        $handler3 = $this->getMockBuilder(GelfHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler3->expects(self::never())
            ->method('setFormatter');
        $handler3->expects(self::never())
            ->method('getFormatter');

        $monologHandlerPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::exactly(3))
            ->method('get')
            ->withConsecutive([FirePHPHandler::class, []], [ChromePHPHandler::class, []], [GelfHandler::class, []])
            ->willReturnOnConsecutiveCalls($handler1, $handler2, $handler3);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(3))
            ->method('get')
            ->with(MonologHandlerPluginManager::class)
            ->willReturn($monologHandlerPluginManager);

        $factory = new WhatFailureGroupHandlerFactory();

        $handler = $factory($container, '', ['handlers' => $handlers, 'bubble' => false, 'formatter' => $formatter]);

        self::assertInstanceOf(WhatFailureGroupHandler::class, $handler);

        $fp = new ReflectionProperty($handler, 'handlers');
        $fp->setAccessible(true);

        $handlerClasses = $fp->getValue($handler);

        self::assertIsArray($handlerClasses);
        self::assertCount(3, $handlerClasses);
        self::assertSame($handler1, $handlerClasses[0]);
        self::assertSame($handler2, $handlerClasses[1]);
        self::assertSame($handler3, $handlerClasses[2]);

        $bubble = new ReflectionProperty($handler, 'bubble');
        $bubble->setAccessible(true);

        self::assertFalse($bubble->getValue($handler));

        $proc = new ReflectionProperty($handler, 'processors');
        $proc->setAccessible(true);

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     */
    public function testInvokeWithConfigAndBoolProcessors(): void
    {
        $processors = true;
        $handlers   = [
            [
                'type' => FingersCrossedHandler::class,
                'enabled' => false,
            ],
            [
                'type' => FirePHPHandler::class,
                'enabled' => true,
            ],
            [
                'type' => ChromePHPHandler::class,
            ],
            [
                'type' => GelfHandler::class,
            ],
        ];

        $handler1 = $this->getMockBuilder(FirePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler1->expects(self::never())
            ->method('setFormatter');
        $handler1->expects(self::never())
            ->method('getFormatter');

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler2->expects(self::never())
            ->method('setFormatter');
        $handler2->expects(self::never())
            ->method('getFormatter');

        $handler3 = $this->getMockBuilder(GelfHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler3->expects(self::never())
            ->method('setFormatter');
        $handler3->expects(self::never())
            ->method('getFormatter');

        $monologHandlerPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::exactly(3))
            ->method('get')
            ->withConsecutive([FirePHPHandler::class, []], [ChromePHPHandler::class, []], [GelfHandler::class, []])
            ->willReturnOnConsecutiveCalls($handler1, $handler2, $handler3);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(3))
            ->method('get')
            ->with(MonologHandlerPluginManager::class)
            ->willReturn($monologHandlerPluginManager);

        $factory = new WhatFailureGroupHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Processors must be an Array');

        $factory($container, '', ['handlers' => $handlers, 'bubble' => false, 'processors' => $processors]);
    }
}
