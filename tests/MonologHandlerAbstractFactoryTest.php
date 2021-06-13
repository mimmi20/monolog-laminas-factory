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

namespace Mimmi20Test\LoggerFactory;

use Cascader\Cascader;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Mimmi20\LoggerFactory\MonologFormatterPluginManager;
use Mimmi20\LoggerFactory\MonologHandlerAbstractFactory;
use Mimmi20\LoggerFactory\MonologProcessorPluginManager;
use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\ProcessorInterface;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

use function sprintf;

final class MonologHandlerAbstractFactoryTest extends TestCase
{
    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testCanCreate(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $factory   = new MonologHandlerAbstractFactory();

        self::assertFalse($factory->canCreate($container, 'DoesNotExist'));
        self::assertTrue($factory->canCreate($container, StreamHandler::class));
    }

    /**
     * @throws Exception
     */
    public function testInvoceException(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::once())
            ->method('get')
            ->with(Cascader::class)
            ->willThrowException(new ServiceNotFoundException());

        $factory = new MonologHandlerAbstractFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionMessage(sprintf('Could not find service %s', Cascader::class));
        $this->expectExceptionCode(0);

        $factory($container, '');
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testInvoce(): void
    {
        $requestedName = StreamHandler::class;
        $parameters    = ['abc' => 'xyz'];
        $options       = ['parameters' => $parameters];

        $handler = $this->createMock(HandlerInterface::class);

        $cascader = $this->getMockBuilder(Cascader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cascader->expects(self::once())
            ->method('create')
            ->with($requestedName, $parameters)
            ->willReturn($handler);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::once())
            ->method('get')
            ->with(Cascader::class)
            ->willReturn($cascader);

        $factory = new MonologHandlerAbstractFactory();

        self::assertSame($handler, $factory($container, $requestedName, $options));
    }

    /**
     * @throws Exception
     */
    public function testInvoceWithProcessors(): void
    {
        $requestedName = Logger::class;
        $options       = ['name' => 'xyz', 'processors' => 'fake'];

        $handler = $this->getMockBuilder(StreamHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler->expects(self::never())
            ->method('pushProcessor');
        $handler->expects(self::never())
            ->method('setFormatter');

        $cascader = $this->getMockBuilder(Cascader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cascader->expects(self::once())
            ->method('create')
            ->with($requestedName, [])
            ->willReturn($handler);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::once())
            ->method('get')
            ->with(Cascader::class)
            ->willReturn($cascader);

        $factory = new MonologHandlerAbstractFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('Processors must be an Array');
        $this->expectExceptionCode(0);

        $factory($container, $requestedName, $options);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     */
    public function testInvoceWithProcessors2(): void
    {
        $requestedName = Logger::class;
        $options       = ['name' => 'xyz', 'processors' => [[]]];

        $handler = $this->getMockBuilder(StreamHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler->expects(self::never())
            ->method('pushProcessor');
        $handler->expects(self::never())
            ->method('setFormatter');

        $cascader = $this->getMockBuilder(Cascader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cascader->expects(self::once())
            ->method('create')
            ->with($requestedName, [])
            ->willReturn($handler);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive([Cascader::class], [MonologProcessorPluginManager::class])
            ->willReturnCallback(
                static function ($param) use ($cascader) {
                    if (Cascader::class === $param) {
                        return $cascader;
                    }

                    throw new ServiceNotFoundException();
                }
            );

        $factory = new MonologHandlerAbstractFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionMessage(sprintf('Could not find service %s', MonologProcessorPluginManager::class));
        $this->expectExceptionCode(0);

        $factory($container, $requestedName, $options);
    }

    /**
     * @throws Exception
     */
    public function testInvoceWithProcessors3(): void
    {
        $requestedName = Logger::class;
        $options       = ['name' => 'xyz', 'processors' => [['enabled' => true]]];

        $monologProcessorPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologProcessorPluginManager->expects(self::never())
            ->method('has');
        $monologProcessorPluginManager->expects(self::never())
            ->method('get');

        $handler = $this->getMockBuilder(StreamHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler->expects(self::never())
            ->method('pushProcessor');
        $handler->expects(self::never())
            ->method('setFormatter');

        $cascader = $this->getMockBuilder(Cascader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cascader->expects(self::once())
            ->method('create')
            ->with($requestedName, [])
            ->willReturn($handler);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive([Cascader::class], [MonologProcessorPluginManager::class])
            ->willReturnOnConsecutiveCalls($cascader, $monologProcessorPluginManager);

        $factory = new MonologHandlerAbstractFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('Options must contain a name for the processor');
        $this->expectExceptionCode(0);

        $factory($container, $requestedName, $options);
    }

    /**
     * @throws Exception
     */
    public function testInvoceWithProcessors4(): void
    {
        $requestedName = Logger::class;
        $options       = [
            'name' => 'xyz',
            'processors' => [
                ['enabled' => false],
                [
                    'enabled' => true,
                    'name' => 'xyz',
                ],
                ['name' => 'abc'],
            ],
        ];

        $monologProcessorPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologProcessorPluginManager->expects(self::never())
            ->method('has');
        $monologProcessorPluginManager->expects(self::once())
            ->method('get')
            ->with('abc', [])
            ->willThrowException(new ServiceNotFoundException());

        $handler = $this->getMockBuilder(StreamHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler->expects(self::never())
            ->method('pushProcessor');
        $handler->expects(self::never())
            ->method('setFormatter');

        $cascader = $this->getMockBuilder(Cascader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cascader->expects(self::once())
            ->method('create')
            ->with($requestedName, [])
            ->willReturn($handler);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive([Cascader::class], [MonologProcessorPluginManager::class])
            ->willReturnOnConsecutiveCalls($cascader, $monologProcessorPluginManager);

        $factory = new MonologHandlerAbstractFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionMessage(sprintf('Could not find service %s', 'abc'));
        $this->expectExceptionCode(0);

        $factory($container, $requestedName, $options);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testInvoceWithProcessors5(): void
    {
        $requestedName = Logger::class;
        $function      = static fn (array $record): array => $record;
        $options       = [
            'name' => 'xyz',
            'processors' => [
                ['enabled' => false],
                [
                    'enabled' => true,
                    'name' => 'xyz',
                    'parameters' => ['efg' => 'ijk'],
                ],
                ['name' => 'abc'],
                $function,
            ],
        ];

        $processor = $this->createMock(ProcessorInterface::class);

        $monologProcessorPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologProcessorPluginManager->expects(self::never())
            ->method('has');
        $monologProcessorPluginManager->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive(['abc', []], ['xyz', ['efg' => 'ijk']])
            ->willReturn($processor);

        $handler = $this->getMockBuilder(StreamHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler->expects(self::exactly(3))
            ->method('pushProcessor')
            ->withConsecutive([$function], [$processor], [$processor]);
        $handler->expects(self::never())
            ->method('setFormatter');

        $cascader = $this->getMockBuilder(Cascader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cascader->expects(self::once())
            ->method('create')
            ->with($requestedName, [])
            ->willReturn($handler);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive([Cascader::class], [MonologProcessorPluginManager::class])
            ->willReturnOnConsecutiveCalls($cascader, $monologProcessorPluginManager);

        $factory = new MonologHandlerAbstractFactory();

        $handler = $factory($container, $requestedName, $options);

        self::assertInstanceOf(HandlerInterface::class, $handler);
    }

    /**
     * @throws Exception
     */
    public function testInvoceWithFormatter(): void
    {
        $requestedName = Logger::class;
        $options       = ['name' => 'xyz', 'formatter' => 'fake'];

        $handler = $this->getMockBuilder(StreamHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler->expects(self::never())
            ->method('pushProcessor');
        $handler->expects(self::never())
            ->method('setFormatter');

        $cascader = $this->getMockBuilder(Cascader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cascader->expects(self::once())
            ->method('create')
            ->with($requestedName, [])
            ->willReturn($handler);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::once())
            ->method('get')
            ->with(Cascader::class)
            ->willReturn($cascader);

        $factory = new MonologHandlerAbstractFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage(sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class));
        $this->expectExceptionCode(0);

        $factory($container, $requestedName, $options);
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     */
    public function testInvoceWithFormatter2(): void
    {
        $requestedName = Logger::class;
        $options       = ['name' => 'xyz', 'formatter' => [[]]];

        $handler = $this->getMockBuilder(StreamHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler->expects(self::never())
            ->method('pushProcessor');
        $handler->expects(self::never())
            ->method('setFormatter');

        $cascader = $this->getMockBuilder(Cascader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cascader->expects(self::once())
            ->method('create')
            ->with($requestedName, [])
            ->willReturn($handler);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive([Cascader::class], [MonologFormatterPluginManager::class])
            ->willReturnCallback(
                static function ($param) use ($cascader) {
                    if (Cascader::class === $param) {
                        return $cascader;
                    }

                    throw new ServiceNotFoundException();
                }
            );

        $factory = new MonologHandlerAbstractFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionMessage(sprintf('Could not find service %s', MonologFormatterPluginManager::class));
        $this->expectExceptionCode(0);

        $factory($container, $requestedName, $options);
    }

    /**
     * @throws Exception
     */
    public function testInvoceWithFormatter3(): void
    {
        $requestedName = Logger::class;
        $options       = ['name' => 'xyz', 'formatter' => [['enabled' => true]]];

        $monologFormatterPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologFormatterPluginManager->expects(self::never())
            ->method('has');
        $monologFormatterPluginManager->expects(self::never())
            ->method('get');

        $handler = $this->getMockBuilder(StreamHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler->expects(self::never())
            ->method('pushProcessor');
        $handler->expects(self::never())
            ->method('setFormatter');

        $cascader = $this->getMockBuilder(Cascader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cascader->expects(self::once())
            ->method('create')
            ->with($requestedName, [])
            ->willReturn($handler);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive([Cascader::class], [MonologFormatterPluginManager::class])
            ->willReturnOnConsecutiveCalls($cascader, $monologFormatterPluginManager);

        $factory = new MonologHandlerAbstractFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('Options must contain a name for the formatter');
        $this->expectExceptionCode(0);

        $factory($container, $requestedName, $options);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testInvoceWithFormatter4(): void
    {
        $requestedName = Logger::class;
        $options       = [
            'name' => 'xyz',
            'formatter' => ['enabled' => false],
        ];

        $monologFormatterPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologFormatterPluginManager->expects(self::never())
            ->method('has');
        $monologFormatterPluginManager->expects(self::never())
            ->method('get');

        $handler = $this->getMockBuilder(StreamHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler->expects(self::never())
            ->method('pushProcessor');
        $handler->expects(self::never())
            ->method('setFormatter');

        $cascader = $this->getMockBuilder(Cascader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cascader->expects(self::once())
            ->method('create')
            ->with($requestedName, [])
            ->willReturn($handler);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive([Cascader::class], [MonologFormatterPluginManager::class])
            ->willReturnOnConsecutiveCalls($cascader, $monologFormatterPluginManager);

        $factory = new MonologHandlerAbstractFactory();

        $handler = $factory($container, $requestedName, $options);

        self::assertInstanceOf(HandlerInterface::class, $handler);
    }

    /**
     * @throws Exception
     */
    public function testInvoceWithFormatter5(): void
    {
        $requestedName = Logger::class;
        $options       = [
            'name' => 'xyz',
            'formatter' => [
                'enabled' => true,
                'name' => 'xyz',
            ],
        ];

        $monologFormatterPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologFormatterPluginManager->expects(self::never())
            ->method('has');
        $monologFormatterPluginManager->expects(self::once())
            ->method('get')
            ->with('xyz', [])
            ->willThrowException(new ServiceNotFoundException());

        $handler = $this->getMockBuilder(StreamHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler->expects(self::never())
            ->method('pushProcessor');
        $handler->expects(self::never())
            ->method('setFormatter');

        $cascader = $this->getMockBuilder(Cascader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cascader->expects(self::once())
            ->method('create')
            ->with($requestedName, [])
            ->willReturn($handler);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive([Cascader::class], [MonologFormatterPluginManager::class])
            ->willReturnOnConsecutiveCalls($cascader, $monologFormatterPluginManager);

        $factory = new MonologHandlerAbstractFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionMessage(sprintf('Could not find service %s', 'xyz'));
        $this->expectExceptionCode(0);

        $factory($container, $requestedName, $options);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testInvoceWithFormatter6(): void
    {
        $requestedName = Logger::class;
        $function      = static fn (array $record): array => $record;
        $options       = [
            'name' => 'xyz',
            'formatter' => [
                'enabled' => true,
                'name' => 'xyz',
                'parameters' => [],
            ],
        ];

        $formatter = $this->createMock(FormatterInterface::class);

        $monologFormatterPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologFormatterPluginManager->expects(self::never())
            ->method('has');
        $monologFormatterPluginManager->expects(self::once())
            ->method('get')
            ->with('xyz', [])
            ->willReturn($formatter);

        $handler = $this->getMockBuilder(StreamHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler->expects(self::never())
            ->method('pushProcessor');
        $handler->expects(self::once())
            ->method('setFormatter')
            ->with($formatter);

        $cascader = $this->getMockBuilder(Cascader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cascader->expects(self::once())
            ->method('create')
            ->with($requestedName, [])
            ->willReturn($handler);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive([Cascader::class], [MonologFormatterPluginManager::class])
            ->willReturnOnConsecutiveCalls($cascader, $monologFormatterPluginManager);

        $factory = new MonologHandlerAbstractFactory();

        $handler = $factory($container, $requestedName, $options);

        self::assertInstanceOf(HandlerInterface::class, $handler);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testInvoceWithFormatter7(): void
    {
        $requestedName = Logger::class;
        $options       = [
            'name' => 'xyz',
            'formatter' => ['name' => 'abc'],
        ];

        $formatter = $this->createMock(FormatterInterface::class);

        $monologFormatterPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologFormatterPluginManager->expects(self::never())
            ->method('has');
        $monologFormatterPluginManager->expects(self::once())
            ->method('get')
            ->with('abc', [])
            ->willReturn($formatter);

        $handler = $this->getMockBuilder(StreamHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler->expects(self::never())
            ->method('pushProcessor');
        $handler->expects(self::once())
            ->method('setFormatter')
            ->with($formatter);

        $cascader = $this->getMockBuilder(Cascader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cascader->expects(self::once())
            ->method('create')
            ->with($requestedName, [])
            ->willReturn($handler);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive([Cascader::class], [MonologFormatterPluginManager::class])
            ->willReturnOnConsecutiveCalls($cascader, $monologFormatterPluginManager);

        $factory = new MonologHandlerAbstractFactory();

        $handler = $factory($container, $requestedName, $options);

        self::assertInstanceOf(HandlerInterface::class, $handler);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testInvoceWithFormatter8(): void
    {
        $requestedName = Logger::class;
        $formatter     = $this->createMock(FormatterInterface::class);
        $options       = [
            'name' => 'xyz',
            'formatter' => $formatter,
        ];

        $monologFormatterPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologFormatterPluginManager->expects(self::never())
            ->method('has');
        $monologFormatterPluginManager->expects(self::never())
            ->method('get');

        $handler = $this->getMockBuilder(StreamHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler->expects(self::never())
            ->method('pushProcessor');
        $handler->expects(self::once())
            ->method('setFormatter')
            ->with($formatter);

        $cascader = $this->getMockBuilder(Cascader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cascader->expects(self::once())
            ->method('create')
            ->with($requestedName, [])
            ->willReturn($handler);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive([Cascader::class], [MonologFormatterPluginManager::class])
            ->willReturnOnConsecutiveCalls($cascader, $monologFormatterPluginManager);

        $factory = new MonologHandlerAbstractFactory();

        $handler = $factory($container, $requestedName, $options);

        self::assertInstanceOf(HandlerInterface::class, $handler);
    }
}
