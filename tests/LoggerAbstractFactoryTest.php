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

use DateTimeZone;
use Interop\Container\ContainerInterface;
use Laminas\Log\Logger;
use Laminas\Log\Processor\RequestId;
use Laminas\Log\ProcessorPluginManager;
use Laminas\Log\Writer\Noop;
use Laminas\Log\Writer\Psr;
use Laminas\Log\Writer\WriterInterface;
use Laminas\Log\WriterPluginManager;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\Stdlib\SplPriorityQueue;
use Mimmi20\LoggerFactory\LoggerAbstractFactory;
use Mimmi20\LoggerFactory\MonologPluginManager;
use Monolog\Handler\HandlerInterface;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionProperty;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

use function sprintf;

final class LoggerAbstractFactoryTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testInvoceWithoutConfig(): void
    {
        $requestedName = Logger::class;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with('config')
            ->willThrowException(new ServiceNotFoundException());

        $factory = new LoggerAbstractFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionMessage(sprintf('Could not find service %s', 'config'));
        $this->expectExceptionCode(0);

        $factory($container, $requestedName, null);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testInvoceWithEmptyConfig(): void
    {
        $requestedName = Logger::class;
        $config        = [];

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::exactly(2))
            ->method('has')
            ->withConsecutive(['LogProcessorManager'], ['LogWriterManager'])
            ->willReturnOnConsecutiveCalls(false, false);
        $container->expects(self::once())
            ->method('get')
            ->with('config')
            ->willReturn($config);

        $factory = new LoggerAbstractFactory();

        $logger = $factory($container, $requestedName, null);

        self::assertInstanceOf(Logger::class, $logger);
        self::assertInstanceOf(SplPriorityQueue::class, $logger->getWriters());
        self::assertCount(1, $logger->getWriters());
        self::assertInstanceOf(SplPriorityQueue::class, $logger->getProcessors());
        self::assertCount(0, $logger->getProcessors());
        self::assertInstanceOf(WriterPluginManager::class, $logger->getWriterPluginManager());
        self::assertInstanceOf(ProcessorPluginManager::class, $logger->getProcessorPluginManager());
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testInvoceWithConfig(): void
    {
        $requestedName = Logger::class;
        $config        = [
            'log' => [
                'exceptionhandler' => true,
                'errorhandler' => true,
                'fatal_error_shutdownfunction' => true,
            ],
        ];

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::exactly(2))
            ->method('has')
            ->withConsecutive(['LogProcessorManager'], ['LogWriterManager'])
            ->willReturnOnConsecutiveCalls(false, false);
        $container->expects(self::once())
            ->method('get')
            ->with('config')
            ->willReturn($config);

        $factory = new LoggerAbstractFactory();

        $logger = $factory($container, $requestedName, null);

        self::assertInstanceOf(Logger::class, $logger);
        self::assertInstanceOf(SplPriorityQueue::class, $logger->getWriters());
        self::assertCount(1, $logger->getWriters());
        self::assertInstanceOf(SplPriorityQueue::class, $logger->getProcessors());
        self::assertCount(0, $logger->getProcessors());
        self::assertInstanceOf(WriterPluginManager::class, $logger->getWriterPluginManager());
        self::assertInstanceOf(ProcessorPluginManager::class, $logger->getProcessorPluginManager());
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testInvoceWithConfig2(): void
    {
        $requestedName = Logger::class;
        $config        = [
            'log' => [
                $requestedName => [
                    'exceptionhandler' => true,
                    'errorhandler' => true,
                    'fatal_error_shutdownfunction' => true,
                ],
            ],
        ];

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::exactly(2))
            ->method('has')
            ->withConsecutive(['LogProcessorManager'], ['LogWriterManager'])
            ->willReturnOnConsecutiveCalls(false, false);
        $container->expects(self::once())
            ->method('get')
            ->with('config')
            ->willReturn($config);

        $factory = new LoggerAbstractFactory();

        $logger = $factory($container, $requestedName, null);

        self::assertInstanceOf(Logger::class, $logger);
        self::assertInstanceOf(SplPriorityQueue::class, $logger->getWriters());
        self::assertCount(1, $logger->getWriters());
        self::assertInstanceOf(SplPriorityQueue::class, $logger->getProcessors());
        self::assertCount(0, $logger->getProcessors());
        self::assertInstanceOf(WriterPluginManager::class, $logger->getWriterPluginManager());
        self::assertInstanceOf(ProcessorPluginManager::class, $logger->getProcessorPluginManager());
    }

    /**
     * @throws Exception
     */
    public function testInvoceWithConfigException(): void
    {
        $requestedName = Logger::class;
        $config        = [
            'log' => [
                $requestedName => [
                    'exceptionhandler' => true,
                    'errorhandler' => true,
                    'fatal_error_shutdownfunction' => true,
                ],
            ],
        ];

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::once())
            ->method('has')
            ->with('LogProcessorManager')
            ->willReturn(true);
        $container->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive(['config'], ['LogProcessorManager'])
            ->willReturnCallback(
                static function ($param) use ($config) {
                    if ('config' === $param) {
                        return $config;
                    }

                    throw new ServiceNotFoundException();
                }
            );

        $factory = new LoggerAbstractFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('An error occured while setting the ProcessorPluginManager');
        $this->expectExceptionCode(0);

        $factory($container, $requestedName, null);
    }

    /**
     * @throws Exception
     */
    public function testInvoceWithConfigException2(): void
    {
        $requestedName = Logger::class;
        $config        = [
            'log' => [
                $requestedName => [
                    'exceptionhandler' => true,
                    'errorhandler' => true,
                    'fatal_error_shutdownfunction' => true,
                ],
            ],
        ];

        $processorPluginManager = $this->getMockBuilder(ProcessorPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $processorPluginManager->expects(self::never())
            ->method('has');
        $processorPluginManager->expects(self::never())
            ->method('get');

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::exactly(2))
            ->method('has')
            ->withConsecutive(['LogProcessorManager'], ['LogWriterManager'])
            ->willReturnOnConsecutiveCalls(true, true);
        $container->expects(self::exactly(3))
            ->method('get')
            ->withConsecutive(['config'], ['LogProcessorManager'], ['LogWriterManager'])
            ->willReturnCallback(
                static function ($param) use ($config, $processorPluginManager) {
                    if ('config' === $param) {
                        return $config;
                    }

                    if ('LogProcessorManager' === $param) {
                        return $processorPluginManager;
                    }

                    throw new ServiceNotFoundException();
                }
            );

        $factory = new LoggerAbstractFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('An error occured while setting the LogWriterManager');
        $this->expectExceptionCode(0);

        $factory($container, $requestedName, null);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testInvoceWithConfig3(): void
    {
        $requestedName = Logger::class;
        $config        = [
            'log' => [
                $requestedName => [
                    'exceptionhandler' => true,
                    'errorhandler' => true,
                    'fatal_error_shutdownfunction' => true,
                ],
            ],
        ];

        $processorPluginManager = $this->getMockBuilder(ProcessorPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $processorPluginManager->expects(self::never())
            ->method('has');
        $processorPluginManager->expects(self::never())
            ->method('get');

        $writerPluginManager = $this->getMockBuilder(WriterPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $writerPluginManager->expects(self::never())
            ->method('has');
        $writerPluginManager->expects(self::never())
            ->method('get');

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::exactly(2))
            ->method('has')
            ->withConsecutive(['LogProcessorManager'], ['LogWriterManager'])
            ->willReturnOnConsecutiveCalls(true, true);
        $container->expects(self::exactly(3))
            ->method('get')
            ->withConsecutive(['config'], ['LogProcessorManager'], ['LogWriterManager'])
            ->willReturnOnConsecutiveCalls($config, $processorPluginManager, $writerPluginManager);

        $factory = new LoggerAbstractFactory();

        $logger = $factory($container, $requestedName, null);

        self::assertInstanceOf(Logger::class, $logger);
        self::assertInstanceOf(SplPriorityQueue::class, $logger->getWriters());
        self::assertCount(1, $logger->getWriters());
        self::assertInstanceOf(SplPriorityQueue::class, $logger->getProcessors());
        self::assertCount(0, $logger->getProcessors());
        self::assertSame($writerPluginManager, $logger->getWriterPluginManager());
        self::assertSame($processorPluginManager, $logger->getProcessorPluginManager());
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testInvoceWithConfig4(): void
    {
        $requestedName = Logger::class;
        $config        = [
            'log' => [
                $requestedName => [
                    'exceptionhandler' => true,
                    'errorhandler' => true,
                    'fatal_error_shutdownfunction' => true,
                    'writers' => [
                        ['enabled' => false],
                        ['name' => true],
                        [
                            'enabled' => true,
                            'name' => 'xyz',
                            'options' => ['efg' => 'ijk'],
                        ],
                        ['name' => 'abc'],
                    ],
                ],
            ],
        ];

        $processorPluginManager = $this->getMockBuilder(ProcessorPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $processorPluginManager->expects(self::never())
            ->method('has');
        $processorPluginManager->expects(self::never())
            ->method('get');

        $writer = $this->getMockBuilder(WriterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $writerPluginManager = $this->getMockBuilder(WriterPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $writerPluginManager->expects(self::never())
            ->method('has');
        $writerPluginManager->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive(['xyz', ['efg' => 'ijk']], ['abc', null])
            ->willReturn($writer);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::exactly(2))
            ->method('has')
            ->withConsecutive(['LogProcessorManager'], ['LogWriterManager'])
            ->willReturnOnConsecutiveCalls(true, true);
        $container->expects(self::exactly(3))
            ->method('get')
            ->withConsecutive(['config'], ['LogProcessorManager'], ['LogWriterManager'])
            ->willReturnOnConsecutiveCalls($config, $processorPluginManager, $writerPluginManager);

        $factory = new LoggerAbstractFactory();

        $logger = $factory($container, $requestedName, null);

        self::assertInstanceOf(Logger::class, $logger);
        self::assertInstanceOf(SplPriorityQueue::class, $logger->getWriters());
        self::assertCount(3, $logger->getWriters());
        self::assertInstanceOf(SplPriorityQueue::class, $logger->getProcessors());
        self::assertCount(0, $logger->getProcessors());
        self::assertSame($writerPluginManager, $logger->getWriterPluginManager());
        self::assertSame($processorPluginManager, $logger->getProcessorPluginManager());
    }

    /**
     * @throws Exception
     */
    public function testInvoceWithConfig5(): void
    {
        $requestedName = Logger::class;
        $config        = [
            'log' => [
                $requestedName => [
                    'exceptionhandler' => true,
                    'errorhandler' => true,
                    'fatal_error_shutdownfunction' => true,
                    'writers' => [
                        ['enabled' => false],
                        ['name' => true],
                        ['enabled' => true],
                        ['name' => 'abc'],
                    ],
                ],
            ],
        ];

        $processorPluginManager = $this->getMockBuilder(ProcessorPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $processorPluginManager->expects(self::never())
            ->method('has');
        $processorPluginManager->expects(self::never())
            ->method('get');

        $writerPluginManager = $this->getMockBuilder(WriterPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $writerPluginManager->expects(self::never())
            ->method('has');
        $writerPluginManager->expects(self::never())
            ->method('get');

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::exactly(2))
            ->method('has')
            ->withConsecutive(['LogProcessorManager'], ['LogWriterManager'])
            ->willReturnOnConsecutiveCalls(true, true);
        $container->expects(self::exactly(3))
            ->method('get')
            ->withConsecutive(['config'], ['LogProcessorManager'], ['LogWriterManager'])
            ->willReturnOnConsecutiveCalls($config, $processorPluginManager, $writerPluginManager);

        $factory = new LoggerAbstractFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('Options must contain a name for the writer');
        $this->expectExceptionCode(0);

        $factory($container, $requestedName, null);
    }

    /**
     * @throws Exception
     */
    public function testInvoceWithConfig6(): void
    {
        $requestedName = Logger::class;
        $config        = [
            'log' => [
                $requestedName => [
                    'exceptionhandler' => true,
                    'errorhandler' => true,
                    'fatal_error_shutdownfunction' => true,
                    'writers' => [
                        ['enabled' => false],
                        ['name' => true],
                        [
                            'enabled' => true,
                            'name' => 'xyz',
                        ],
                        ['name' => 'abc'],
                    ],
                ],
            ],
        ];

        $processorPluginManager = $this->getMockBuilder(ProcessorPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $processorPluginManager->expects(self::never())
            ->method('has');
        $processorPluginManager->expects(self::never())
            ->method('get');

        $writerPluginManager = $this->getMockBuilder(WriterPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $writerPluginManager->expects(self::never())
            ->method('has');
        $writerPluginManager->expects(self::once())
            ->method('get')
            ->with('xyz', null)
            ->willThrowException(new ServiceNotCreatedException());

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::exactly(2))
            ->method('has')
            ->withConsecutive(['LogProcessorManager'], ['LogWriterManager'])
            ->willReturnOnConsecutiveCalls(true, true);
        $container->expects(self::exactly(3))
            ->method('get')
            ->withConsecutive(['config'], ['LogProcessorManager'], ['LogWriterManager'])
            ->willReturnOnConsecutiveCalls($config, $processorPluginManager, $writerPluginManager);

        $factory = new LoggerAbstractFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('An error occured while adding a writer');
        $this->expectExceptionCode(0);

        $factory($container, $requestedName, null);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testInvoceWithConfig7(): void
    {
        $requestedName = Logger::class;
        $config        = [
            'log' => [
                $requestedName => [
                    'exceptionhandler' => true,
                    'errorhandler' => true,
                    'fatal_error_shutdownfunction' => true,
                    'processors' => [
                        ['enabled' => false],
                        ['name' => true],
                        [
                            'enabled' => true,
                            'name' => 'xyz',
                            'options' => ['efg' => 'ijk'],
                        ],
                        ['name' => 'abc'],
                    ],
                ],
            ],
        ];

        $processor = $this->getMockBuilder(WriterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $processorPluginManager = $this->getMockBuilder(ProcessorPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $processorPluginManager->expects(self::never())
            ->method('has');
        $processorPluginManager->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive(['xyz', ['efg' => 'ijk']], ['abc', null])
            ->willReturn($processor);

        $writerPluginManager = $this->getMockBuilder(WriterPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $writerPluginManager->expects(self::never())
            ->method('has');
        $writerPluginManager->expects(self::never())
            ->method('get');

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::exactly(2))
            ->method('has')
            ->withConsecutive(['LogProcessorManager'], ['LogWriterManager'])
            ->willReturnOnConsecutiveCalls(true, true);
        $container->expects(self::exactly(3))
            ->method('get')
            ->withConsecutive(['config'], ['LogProcessorManager'], ['LogWriterManager'])
            ->willReturnOnConsecutiveCalls($config, $processorPluginManager, $writerPluginManager);

        $factory = new LoggerAbstractFactory();

        $logger = $factory($container, $requestedName, null);

        self::assertInstanceOf(Logger::class, $logger);
        self::assertInstanceOf(SplPriorityQueue::class, $logger->getWriters());
        self::assertCount(1, $logger->getWriters());
        self::assertInstanceOf(SplPriorityQueue::class, $logger->getProcessors());
        self::assertCount(2, $logger->getProcessors());
        self::assertSame($writerPluginManager, $logger->getWriterPluginManager());
        self::assertSame($processorPluginManager, $logger->getProcessorPluginManager());
    }

    /**
     * @throws Exception
     */
    public function testInvoceWithConfig8(): void
    {
        $requestedName = Logger::class;
        $config        = [
            'log' => [
                $requestedName => [
                    'exceptionhandler' => true,
                    'errorhandler' => true,
                    'fatal_error_shutdownfunction' => true,
                    'processors' => [
                        ['enabled' => false],
                        ['name' => true],
                        ['enabled' => true],
                        ['name' => 'abc'],
                    ],
                ],
            ],
        ];

        $processorPluginManager = $this->getMockBuilder(ProcessorPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $processorPluginManager->expects(self::never())
            ->method('has');
        $processorPluginManager->expects(self::never())
            ->method('get');

        $writerPluginManager = $this->getMockBuilder(WriterPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $writerPluginManager->expects(self::never())
            ->method('has');
        $writerPluginManager->expects(self::never())
            ->method('get');

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::exactly(2))
            ->method('has')
            ->withConsecutive(['LogProcessorManager'], ['LogWriterManager'])
            ->willReturnOnConsecutiveCalls(true, true);
        $container->expects(self::exactly(3))
            ->method('get')
            ->withConsecutive(['config'], ['LogProcessorManager'], ['LogWriterManager'])
            ->willReturnOnConsecutiveCalls($config, $processorPluginManager, $writerPluginManager);

        $factory = new LoggerAbstractFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('Options must contain a type for the processor');
        $this->expectExceptionCode(0);

        $factory($container, $requestedName, null);
    }

    /**
     * @throws Exception
     */
    public function testInvoceWithConfig9(): void
    {
        $requestedName = Logger::class;
        $config        = [
            'log' => [
                $requestedName => [
                    'exceptionhandler' => true,
                    'errorhandler' => true,
                    'fatal_error_shutdownfunction' => true,
                    'processors' => [
                        ['enabled' => false],
                        ['name' => true],
                        [
                            'enabled' => true,
                            'name' => 'xyz',
                        ],
                        ['name' => 'abc'],
                    ],
                ],
            ],
        ];

        $processorPluginManager = $this->getMockBuilder(ProcessorPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $processorPluginManager->expects(self::never())
            ->method('has');
        $processorPluginManager->expects(self::once())
            ->method('get')
            ->with('xyz', null)
            ->willThrowException(new ServiceNotCreatedException());

        $writerPluginManager = $this->getMockBuilder(WriterPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $writerPluginManager->expects(self::never())
            ->method('has');
        $writerPluginManager->expects(self::never())
            ->method('get');

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::exactly(2))
            ->method('has')
            ->withConsecutive(['LogProcessorManager'], ['LogWriterManager'])
            ->willReturnOnConsecutiveCalls(true, true);
        $container->expects(self::exactly(3))
            ->method('get')
            ->withConsecutive(['config'], ['LogProcessorManager'], ['LogWriterManager'])
            ->willReturnOnConsecutiveCalls($config, $processorPluginManager, $writerPluginManager);

        $factory = new LoggerAbstractFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('An error occured while adding a processor');
        $this->expectExceptionCode(0);

        $factory($container, $requestedName, null);
    }

    /**
     * @throws Exception
     */
    public function testInvoceWithConfig10a(): void
    {
        $requestedName = Logger::class;
        $name          = 'test-name';
        $config        = [
            'log' => [
                $requestedName => [
                    'exceptionhandler' => true,
                    'errorhandler' => true,
                    'fatal_error_shutdownfunction' => true,
                    'processors' => [
                        ['enabled' => false],
                        ['name' => true],
                        [
                            'enabled' => true,
                            'name' => 'xyz',
                        ],
                        ['name' => 'abc'],
                    ],
                    'name' => $name,
                ],
            ],
        ];

        $processorPluginManager = $this->getMockBuilder(ProcessorPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $processorPluginManager->expects(self::never())
            ->method('has');
        $processorPluginManager->expects(self::once())
            ->method('get')
            ->with('xyz', null)
            ->willThrowException(new ServiceNotCreatedException());

        $writerPluginManager = $this->getMockBuilder(WriterPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $writerPluginManager->expects(self::never())
            ->method('has');
        $writerPluginManager->expects(self::never())
            ->method('get');

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::exactly(2))
            ->method('has')
            ->withConsecutive(['LogProcessorManager'], ['LogWriterManager'])
            ->willReturnOnConsecutiveCalls(true, true);
        $container->expects(self::exactly(3))
            ->method('get')
            ->withConsecutive(['config'], ['LogProcessorManager'], ['LogWriterManager'])
            ->willReturnOnConsecutiveCalls($config, $processorPluginManager, $writerPluginManager);

        $factory = new LoggerAbstractFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('An error occured while adding a processor');
        $this->expectExceptionCode(0);

        $factory($container, $requestedName, null);
    }

    /**
     * @throws Exception
     */
    public function testInvoceWithConfig10b(): void
    {
        $requestedName = Logger::class;
        $name          = 'test-name';
        $processors    = [
            static fn (array $record): array => $record,
        ];
        $handlers      = [
            $this->createMock(HandlerInterface::class),
        ];
        $config        = [
            'log' => [
                $requestedName => [
                    'exceptionhandler' => true,
                    'errorhandler' => true,
                    'fatal_error_shutdownfunction' => true,
                    'name' => $name,
                    'writers' => [
                        ['name' => 'abc'],
                    ],
                    'processors' => [
                        [
                            'enabled' => true,
                            'name' => 'xyz',
                        ],
                    ],
                    'handlers' => $handlers,
                    'monolog_processors' => $processors,
                ],
            ],
        ];

        $processor = $this->getMockBuilder(WriterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $processorPluginManager = $this->getMockBuilder(ProcessorPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $processorPluginManager->expects(self::never())
            ->method('has');
        $processorPluginManager->expects(self::once())
            ->method('get')
            ->with('xyz', null)
            ->willReturn($processor);

        $writer = $this->getMockBuilder(WriterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $writerPluginManager = $this->getMockBuilder(WriterPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $writerPluginManager->expects(self::never())
            ->method('has');
        $writerPluginManager->expects(self::once())
            ->method('get')
            ->with('abc', null)
            ->willReturn($writer);

        $monologPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologPluginManager->expects(self::never())
            ->method('has');
        $monologPluginManager->expects(self::once())
            ->method('get')
            ->with(
                \Monolog\Logger::class,
                [
                    'name' => $name,
                    'handlers' => $handlers,
                    'processors' => $processors,
                ]
            )
            ->willThrowException(new ServiceNotFoundException());

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::exactly(2))
            ->method('has')
            ->withConsecutive(['LogProcessorManager'], ['LogWriterManager'])
            ->willReturnOnConsecutiveCalls(true, true);
        $container->expects(self::exactly(4))
            ->method('get')
            ->withConsecutive(['config'], ['LogProcessorManager'], ['LogWriterManager'], [MonologPluginManager::class])
            ->willReturnOnConsecutiveCalls($config, $processorPluginManager, $writerPluginManager, $monologPluginManager);

        $factory = new LoggerAbstractFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage(sprintf('Could not find service %s', MonologPluginManager::class));
        $this->expectExceptionCode(0);

        $factory($container, $requestedName, null);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testInvoceWithConfig11(): void
    {
        $requestedName = Logger::class;
        $name          = 'test-name';
        $processors    = [
            static fn (array $record): array => $record,
        ];
        $handlers      = [
            $this->createMock(HandlerInterface::class),
        ];
        $config        = [
            'log' => [
                $requestedName => [
                    'exceptionhandler' => true,
                    'errorhandler' => true,
                    'fatal_error_shutdownfunction' => true,
                    'name' => $name,
                    'writers' => [
                        ['name' => 'abc'],
                    ],
                    'processors' => [
                        [
                            'enabled' => true,
                            'name' => 'xyz',
                        ],
                    ],
                    'handlers' => $handlers,
                    'monolog_processors' => $processors,
                ],
            ],
        ];

        $processor = $this->getMockBuilder(WriterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $processorPluginManager = $this->getMockBuilder(ProcessorPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $processorPluginManager->expects(self::never())
            ->method('has');
        $processorPluginManager->expects(self::once())
            ->method('get')
            ->with('xyz', null)
            ->willReturn($processor);

        $writer = $this->getMockBuilder(WriterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $writerPluginManager = $this->getMockBuilder(WriterPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $writerPluginManager->expects(self::never())
            ->method('has');
        $writerPluginManager->expects(self::once())
            ->method('get')
            ->with('abc', null)
            ->willReturn($writer);

        $monolog = $this->getMockBuilder(\Monolog\Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monolog->expects(self::never())
            ->method('setTimezone');

        $monologPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologPluginManager->expects(self::never())
            ->method('has');
        $monologPluginManager->expects(self::once())
            ->method('get')
            ->with(
                \Monolog\Logger::class,
                [
                    'name' => $name,
                    'handlers' => $handlers,
                    'processors' => $processors,
                ]
            )
            ->willReturn($monolog);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::exactly(2))
            ->method('has')
            ->withConsecutive(['LogProcessorManager'], ['LogWriterManager'])
            ->willReturnOnConsecutiveCalls(true, true);
        $container->expects(self::exactly(4))
            ->method('get')
            ->withConsecutive(['config'], ['LogProcessorManager'], ['LogWriterManager'], [MonologPluginManager::class])
            ->willReturnOnConsecutiveCalls($config, $processorPluginManager, $writerPluginManager, $monologPluginManager);

        $factory = new LoggerAbstractFactory();

        $logger = $factory($container, $requestedName, null);

        self::assertInstanceOf(Logger::class, $logger);
        self::assertInstanceOf(SplPriorityQueue::class, $logger->getWriters());
        self::assertCount(3, $logger->getWriters());
        self::assertInstanceOf(SplPriorityQueue::class, $logger->getProcessors());
        self::assertCount(1, $logger->getProcessors());
        self::assertSame($writerPluginManager, $logger->getWriterPluginManager());
        self::assertSame($processorPluginManager, $logger->getProcessorPluginManager());
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testInvoceWithConfig12(): void
    {
        $requestedName = Logger::class;
        $config        = [
            'log' => [
                $requestedName => [
                    'exceptionhandler' => true,
                    'errorhandler' => true,
                    'fatal_error_shutdownfunction' => true,
                    'writers' => [
                        ['name' => 'abc'],
                    ],
                    'processors' => [
                        [
                            'enabled' => true,
                            'name' => 'xyz',
                        ],
                    ],
                    'handlers' => [
                        $this->createMock(HandlerInterface::class),
                    ],
                    'monolog_processors' => [
                        static fn (array $record): array => $record,
                    ],
                ],
            ],
        ];

        $processor = $this->getMockBuilder(WriterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $processorPluginManager = $this->getMockBuilder(ProcessorPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $processorPluginManager->expects(self::never())
            ->method('has');
        $processorPluginManager->expects(self::once())
            ->method('get')
            ->with('xyz', null)
            ->willReturn($processor);

        $writer = $this->getMockBuilder(WriterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $writerPluginManager = $this->getMockBuilder(WriterPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $writerPluginManager->expects(self::never())
            ->method('has');
        $writerPluginManager->expects(self::once())
            ->method('get')
            ->with('abc', null)
            ->willReturn($writer);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::exactly(2))
            ->method('has')
            ->withConsecutive(['LogProcessorManager'], ['LogWriterManager'])
            ->willReturnOnConsecutiveCalls(true, true);
        $container->expects(self::exactly(3))
            ->method('get')
            ->withConsecutive(['config'], ['LogProcessorManager'], ['LogWriterManager'])
            ->willReturnOnConsecutiveCalls($config, $processorPluginManager, $writerPluginManager);

        $factory = new LoggerAbstractFactory();

        $logger = $factory($container, $requestedName, null);

        self::assertInstanceOf(Logger::class, $logger);
        self::assertInstanceOf(SplPriorityQueue::class, $logger->getWriters());
        self::assertCount(2, $logger->getWriters());
        self::assertInstanceOf(SplPriorityQueue::class, $logger->getProcessors());
        self::assertCount(1, $logger->getProcessors());
        self::assertSame($writerPluginManager, $logger->getWriterPluginManager());
        self::assertSame($processorPluginManager, $logger->getProcessorPluginManager());
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testInvoceWithConfig13(): void
    {
        $requestedName = Logger::class;
        $config        = [
            'log' => [
                $requestedName => [
                    'exceptionhandler' => true,
                    'errorhandler' => true,
                    'fatal_error_shutdownfunction' => true,
                    'name' => 'test-name',
                    'writers' => [
                        ['name' => 'abc'],
                    ],
                    'processors' => [
                        [
                            'enabled' => true,
                            'name' => 'xyz',
                        ],
                    ],
                    'monolog_processors' => [
                        static fn (array $record): array => $record,
                    ],
                ],
            ],
        ];

        $processor = $this->getMockBuilder(WriterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $processorPluginManager = $this->getMockBuilder(ProcessorPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $processorPluginManager->expects(self::never())
            ->method('has');
        $processorPluginManager->expects(self::once())
            ->method('get')
            ->with('xyz', null)
            ->willReturn($processor);

        $writer = $this->getMockBuilder(WriterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $writerPluginManager = $this->getMockBuilder(WriterPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $writerPluginManager->expects(self::never())
            ->method('has');
        $writerPluginManager->expects(self::once())
            ->method('get')
            ->with('abc', null)
            ->willReturn($writer);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::exactly(2))
            ->method('has')
            ->withConsecutive(['LogProcessorManager'], ['LogWriterManager'])
            ->willReturnOnConsecutiveCalls(true, true);
        $container->expects(self::exactly(3))
            ->method('get')
            ->withConsecutive(['config'], ['LogProcessorManager'], ['LogWriterManager'])
            ->willReturnOnConsecutiveCalls($config, $processorPluginManager, $writerPluginManager);

        $factory = new LoggerAbstractFactory();

        $logger = $factory($container, $requestedName, null);

        self::assertInstanceOf(Logger::class, $logger);
        self::assertInstanceOf(SplPriorityQueue::class, $logger->getWriters());
        self::assertCount(2, $logger->getWriters());
        self::assertInstanceOf(SplPriorityQueue::class, $logger->getProcessors());
        self::assertCount(1, $logger->getProcessors());
        self::assertSame($writerPluginManager, $logger->getWriterPluginManager());
        self::assertSame($processorPluginManager, $logger->getProcessorPluginManager());
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testInvoceWithConfig14(): void
    {
        $requestedName = Logger::class;
        $config        = [
            'log' => [
                $requestedName => [
                    'exceptionhandler' => true,
                    'errorhandler' => true,
                    'fatal_error_shutdownfunction' => true,
                    'name' => 'test-name',
                    'writers' => [
                        ['name' => 'abc'],
                    ],
                    'processors' => [
                        [
                            'enabled' => true,
                            'name' => 'xyz',
                        ],
                    ],
                    'handlers' => true,
                    'monolog_processors' => [
                        static fn (array $record): array => $record,
                    ],
                ],
            ],
        ];

        $processor = $this->getMockBuilder(WriterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $processorPluginManager = $this->getMockBuilder(ProcessorPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $processorPluginManager->expects(self::never())
            ->method('has');
        $processorPluginManager->expects(self::once())
            ->method('get')
            ->with('xyz', null)
            ->willReturn($processor);

        $writer = $this->getMockBuilder(WriterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $writerPluginManager = $this->getMockBuilder(WriterPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $writerPluginManager->expects(self::never())
            ->method('has');
        $writerPluginManager->expects(self::once())
            ->method('get')
            ->with('abc', null)
            ->willReturn($writer);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::exactly(2))
            ->method('has')
            ->withConsecutive(['LogProcessorManager'], ['LogWriterManager'])
            ->willReturnOnConsecutiveCalls(true, true);
        $container->expects(self::exactly(3))
            ->method('get')
            ->withConsecutive(['config'], ['LogProcessorManager'], ['LogWriterManager'])
            ->willReturnOnConsecutiveCalls($config, $processorPluginManager, $writerPluginManager);

        $factory = new LoggerAbstractFactory();

        $logger = $factory($container, $requestedName, null);

        self::assertInstanceOf(Logger::class, $logger);
        self::assertInstanceOf(SplPriorityQueue::class, $logger->getWriters());
        self::assertCount(2, $logger->getWriters());
        self::assertInstanceOf(SplPriorityQueue::class, $logger->getProcessors());
        self::assertCount(1, $logger->getProcessors());
        self::assertSame($writerPluginManager, $logger->getWriterPluginManager());
        self::assertSame($processorPluginManager, $logger->getProcessorPluginManager());
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws \Laminas\Log\Exception\InvalidArgumentException
     * @throws ReflectionException
     */
    public function testInvoceWithConfig15(): void
    {
        $requestedName = Logger::class;
        $name          = 'test-name';
        $processors    = [
            static fn (array $record): array => $record,
        ];
        $handlers      = [
            $this->createMock(HandlerInterface::class),
        ];
        $timezone      = new DateTimeZone('Europe/London');
        $config        = [
            'log' => [
                $requestedName => [
                    'exceptionhandler' => true,
                    'errorhandler' => true,
                    'fatal_error_shutdownfunction' => true,
                    'name' => $name,
                    'timezone' => $timezone,
                    'writers' => [
                        ['name' => 'abc'],
                        ['name' => true],
                        ['name' => 'vwx'],
                        ['name' => new Noop()],
                    ],
                    'processors' => [
                        [
                            'enabled' => true,
                            'name' => 'xyz',
                        ],
                        [
                            'enabled' => true,
                            'name' => false,
                        ],
                        [
                            'enabled' => true,
                            'name' => 'abcd',
                        ],
                        [
                            'enabled' => true,
                            'name' => new RequestId(),
                        ],
                    ],
                    'handlers' => $handlers,
                    'monolog_processors' => $processors,
                ],
            ],
        ];

        $processor1 = $this->getMockBuilder(WriterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $processor2 = $this->getMockBuilder(WriterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $processorPluginManager = $this->getMockBuilder(ProcessorPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $processorPluginManager->expects(self::never())
            ->method('has');
        $processorPluginManager->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive(['xyz', null], ['abcd', null])
            ->willReturnOnConsecutiveCalls($processor1, $processor2);

        $writer1 = $this->getMockBuilder(WriterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $writer2 = $this->getMockBuilder(WriterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $writerPluginManager = $this->getMockBuilder(WriterPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $writerPluginManager->expects(self::never())
            ->method('has');
        $writerPluginManager->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive(['abc', null], ['vwx', null])
            ->willReturnOnConsecutiveCalls($writer1, $writer2);

        $monolog = $this->getMockBuilder(\Monolog\Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monolog->expects(self::never())
            ->method('setTimezone');

        $monologPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologPluginManager->expects(self::never())
            ->method('has');
        $monologPluginManager->expects(self::once())
            ->method('get')
            ->with(
                \Monolog\Logger::class,
                [
                    'name' => $name,
                    'handlers' => $handlers,
                    'processors' => $processors,
                    'timezone' => $timezone,
                ]
            )
            ->willReturn($monolog);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::exactly(2))
            ->method('has')
            ->withConsecutive(['LogProcessorManager'], ['LogWriterManager'])
            ->willReturnOnConsecutiveCalls(true, true);
        $container->expects(self::exactly(4))
            ->method('get')
            ->withConsecutive(['config'], ['LogProcessorManager'], ['LogWriterManager'], [MonologPluginManager::class])
            ->willReturnOnConsecutiveCalls($config, $processorPluginManager, $writerPluginManager, $monologPluginManager);

        $factory = new LoggerAbstractFactory();

        $logger = $factory($container, $requestedName, null);

        self::assertInstanceOf(Logger::class, $logger);
        self::assertInstanceOf(SplPriorityQueue::class, $logger->getWriters());
        self::assertCount(5, $logger->getWriters());
        self::assertInstanceOf(SplPriorityQueue::class, $logger->getProcessors());
        self::assertCount(3, $logger->getProcessors());
        self::assertSame($writerPluginManager, $logger->getWriterPluginManager());
        self::assertSame($processorPluginManager, $logger->getProcessorPluginManager());

        foreach ($logger->getWriters() as $writer) {
            if (!($writer instanceof Psr)) {
                continue;
            }

            $prop = new ReflectionProperty($writer, 'logger');
            $prop->setAccessible(true);

            $internalLogger = $prop->getValue($writer);

            self::assertSame($monolog, $internalLogger);
        }
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws \Laminas\Log\Exception\InvalidArgumentException
     * @throws ReflectionException
     */
    public function testInvoceWithConfig16(): void
    {
        $requestedName = Logger::class;
        $name          = 'test-name';
        $processors    = [
            static fn (array $record): array => $record,
        ];
        $handlers      = [
            $this->createMock(HandlerInterface::class),
        ];
        $timezone      = 'Europe/London';
        $config        = [
            'log' => [
                $requestedName => [
                    'exceptionhandler' => true,
                    'errorhandler' => true,
                    'fatal_error_shutdownfunction' => true,
                    'name' => $name,
                    'timezone' => $timezone,
                    'writers' => [
                        ['name' => 'abc'],
                        ['name' => true],
                        ['name' => 'vwx'],
                        ['name' => new Noop()],
                    ],
                    'processors' => [
                        [
                            'enabled' => true,
                            'name' => 'xyz',
                        ],
                        [
                            'enabled' => true,
                            'name' => false,
                        ],
                        [
                            'enabled' => true,
                            'name' => 'abcd',
                        ],
                        [
                            'enabled' => true,
                            'name' => new RequestId(),
                        ],
                    ],
                    'handlers' => $handlers,
                    'monolog_processors' => $processors,
                ],
            ],
        ];

        $processor1 = $this->getMockBuilder(WriterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $processor2 = $this->getMockBuilder(WriterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $processorPluginManager = $this->getMockBuilder(ProcessorPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $processorPluginManager->expects(self::never())
            ->method('has');
        $processorPluginManager->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive(['xyz', null], ['abcd', null])
            ->willReturnOnConsecutiveCalls($processor1, $processor2);

        $writer1 = $this->getMockBuilder(WriterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $writer2 = $this->getMockBuilder(WriterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $writerPluginManager = $this->getMockBuilder(WriterPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $writerPluginManager->expects(self::never())
            ->method('has');
        $writerPluginManager->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive(['abc', null], ['vwx', null])
            ->willReturnOnConsecutiveCalls($writer1, $writer2);

        $monolog = $this->getMockBuilder(\Monolog\Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monolog->expects(self::never())
            ->method('setTimezone');

        $monologPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologPluginManager->expects(self::never())
            ->method('has');
        $monologPluginManager->expects(self::once())
            ->method('get')
            ->with(
                \Monolog\Logger::class,
                [
                    'name' => $name,
                    'handlers' => $handlers,
                    'processors' => $processors,
                    'timezone' => $timezone,
                ]
            )
            ->willReturn($monolog);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::exactly(2))
            ->method('has')
            ->withConsecutive(['LogProcessorManager'], ['LogWriterManager'])
            ->willReturnOnConsecutiveCalls(true, true);
        $container->expects(self::exactly(4))
            ->method('get')
            ->withConsecutive(['config'], ['LogProcessorManager'], ['LogWriterManager'], [MonologPluginManager::class])
            ->willReturnOnConsecutiveCalls($config, $processorPluginManager, $writerPluginManager, $monologPluginManager);

        $factory = new LoggerAbstractFactory();

        $logger = $factory($container, $requestedName, null);

        self::assertInstanceOf(Logger::class, $logger);
        self::assertInstanceOf(SplPriorityQueue::class, $logger->getWriters());
        self::assertCount(5, $logger->getWriters());
        self::assertInstanceOf(SplPriorityQueue::class, $logger->getProcessors());
        self::assertCount(3, $logger->getProcessors());
        self::assertSame($writerPluginManager, $logger->getWriterPluginManager());
        self::assertSame($processorPluginManager, $logger->getProcessorPluginManager());

        foreach ($logger->getWriters() as $writer) {
            if (!($writer instanceof Psr)) {
                continue;
            }

            $prop = new ReflectionProperty($writer, 'logger');
            $prop->setAccessible(true);

            $internalLogger = $prop->getValue($writer);

            self::assertSame($monolog, $internalLogger);
        }
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws \Laminas\Log\Exception\InvalidArgumentException
     * @throws ReflectionException
     */
    public function testInvoceWithConfig17(): void
    {
        $requestedName = Logger::class;
        $name          = 'test-name';
        $processors    = [
            static fn (array $record): array => $record,
        ];
        $handlers      = [
            $this->createMock(HandlerInterface::class),
        ];
        $timezone      = 42;
        $config        = [
            'log' => [
                $requestedName => [
                    'exceptionhandler' => true,
                    'errorhandler' => true,
                    'fatal_error_shutdownfunction' => true,
                    'name' => $name,
                    'timezone' => $timezone,
                    'writers' => [
                        ['name' => 'abc'],
                        ['name' => true],
                        ['name' => 'vwx'],
                        ['name' => new Noop()],
                    ],
                    'processors' => [
                        [
                            'enabled' => true,
                            'name' => 'xyz',
                        ],
                        [
                            'enabled' => true,
                            'name' => false,
                        ],
                        [
                            'enabled' => true,
                            'name' => 'abcd',
                        ],
                        [
                            'enabled' => true,
                            'name' => new RequestId(),
                        ],
                    ],
                    'handlers' => $handlers,
                    'monolog_processors' => $processors,
                ],
            ],
        ];

        $processor1 = $this->getMockBuilder(WriterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $processor2 = $this->getMockBuilder(WriterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $processorPluginManager = $this->getMockBuilder(ProcessorPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $processorPluginManager->expects(self::never())
            ->method('has');
        $processorPluginManager->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive(['xyz', null], ['abcd', null])
            ->willReturnOnConsecutiveCalls($processor1, $processor2);

        $writer1 = $this->getMockBuilder(WriterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $writer2 = $this->getMockBuilder(WriterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $writerPluginManager = $this->getMockBuilder(WriterPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $writerPluginManager->expects(self::never())
            ->method('has');
        $writerPluginManager->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive(['abc', null], ['vwx', null])
            ->willReturnOnConsecutiveCalls($writer1, $writer2);

        $monolog = $this->getMockBuilder(\Monolog\Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monolog->expects(self::never())
            ->method('setTimezone');

        $monologPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologPluginManager->expects(self::never())
            ->method('has');
        $monologPluginManager->expects(self::once())
            ->method('get')
            ->with(
                \Monolog\Logger::class,
                [
                    'name' => $name,
                    'handlers' => $handlers,
                    'processors' => $processors,
                ]
            )
            ->willReturn($monolog);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::exactly(2))
            ->method('has')
            ->withConsecutive(['LogProcessorManager'], ['LogWriterManager'])
            ->willReturnOnConsecutiveCalls(true, true);
        $container->expects(self::exactly(4))
            ->method('get')
            ->withConsecutive(['config'], ['LogProcessorManager'], ['LogWriterManager'], [MonologPluginManager::class])
            ->willReturnOnConsecutiveCalls($config, $processorPluginManager, $writerPluginManager, $monologPluginManager);

        $factory = new LoggerAbstractFactory();

        $logger = $factory($container, $requestedName, null);

        self::assertInstanceOf(Logger::class, $logger);
        self::assertInstanceOf(SplPriorityQueue::class, $logger->getWriters());
        self::assertCount(5, $logger->getWriters());
        self::assertInstanceOf(SplPriorityQueue::class, $logger->getProcessors());
        self::assertCount(3, $logger->getProcessors());
        self::assertSame($writerPluginManager, $logger->getWriterPluginManager());
        self::assertSame($processorPluginManager, $logger->getProcessorPluginManager());

        foreach ($logger->getWriters() as $writer) {
            if (!($writer instanceof Psr)) {
                continue;
            }

            $prop = new ReflectionProperty($writer, 'logger');
            $prop->setAccessible(true);

            $internalLogger = $prop->getValue($writer);

            self::assertSame($monolog, $internalLogger);
        }
    }

    /**
     * @throws Exception
     */
    public function testCanCreateWithoutConfig(): void
    {
        $requestedName = Logger::class;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with('config')
            ->willThrowException(new ServiceNotFoundException());

        $factory = new LoggerAbstractFactory();

        $cando = $factory->canCreate($container, $requestedName);

        self::assertFalse($cando);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testCanCreateWithEmptyConfig(): void
    {
        $requestedName = Logger::class;
        $config        = [];

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with('config')
            ->willReturn($config);

        $factory = new LoggerAbstractFactory();

        $cando = $factory->canCreate($container, $requestedName);

        self::assertFalse($cando);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testCanCreateWithConfig(): void
    {
        $requestedName = Logger::class;
        $config        = [
            'log' => [
                'exceptionhandler' => true,
                'errorhandler' => true,
                'fatal_error_shutdownfunction' => true,
            ],
        ];

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with('config')
            ->willReturn($config);

        $factory = new LoggerAbstractFactory();

        $cando = $factory->canCreate($container, $requestedName);

        self::assertFalse($cando);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testCanCreateWithConfig2(): void
    {
        $requestedName = Logger::class;
        $config        = [
            'log' => [
                $requestedName => [
                    'exceptionhandler' => true,
                    'errorhandler' => true,
                    'fatal_error_shutdownfunction' => true,
                ],
            ],
        ];

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with('config')
            ->willReturn($config);

        $factory = new LoggerAbstractFactory();

        $cando = $factory->canCreate($container, $requestedName);

        self::assertTrue($cando);
    }
}
