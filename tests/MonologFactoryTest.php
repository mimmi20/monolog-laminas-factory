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
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Mimmi20\LoggerFactory\MonologFactory;
use Mimmi20\LoggerFactory\MonologHandlerPluginManager;
use Mimmi20\LoggerFactory\MonologProcessorPluginManager;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use Monolog\Processor\ProcessorInterface;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

use function sprintf;

final class MonologFactoryTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testInvoceWithoutname(): void
    {
        $requestedName = Logger::class;
        $options       = ['abc' => 'xyz'];

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new MonologFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('The name for the monolog logger is missing');
        $this->expectExceptionCode(0);

        $factory($container, $requestedName, null);
    }

    /**
     * @throws Exception
     */
    public function testInvoceWithoutname2(): void
    {
        $requestedName = Logger::class;
        $options       = [];

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new MonologFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('The name for the monolog logger is missing');
        $this->expectExceptionCode(0);

        $factory($container, $requestedName, $options);
    }

    /**
     * @throws Exception
     */
    public function testInvoceWithTimezone(): void
    {
        $requestedName = Logger::class;
        $options       = ['name' => 'xyz', 'timezone' => 'Mars/One'];

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new MonologFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('An invalid timezone was set');
        $this->expectExceptionCode(0);

        $factory($container, $requestedName, $options);
    }

    /**
     * @throws Exception
     */
    public function testInvoceWithTimezone2(): void
    {
        $requestedName = Logger::class;
        $options       = ['name' => 'xyz', 'timezone' => true];

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new MonologFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('An invalid timezone was set');
        $this->expectExceptionCode(0);

        $factory($container, $requestedName, $options);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testInvoceWithTimezone3(): void
    {
        $requestedName = Logger::class;
        $options       = ['name' => 'xyz', 'timezone' => 'Europe/Berlin'];

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new MonologFactory();

        $logger = $factory($container, $requestedName, $options);

        self::assertInstanceOf(Logger::class, $logger);
        self::assertInstanceOf(DateTimeZone::class, $logger->getTimezone());
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testInvoceWithTimezone4(): void
    {
        $requestedName = Logger::class;
        $timezone      = new DateTimeZone('Europe/Berlin');
        $options       = ['name' => 'xyz', 'timezone' => $timezone];

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new MonologFactory();

        $logger = $factory($container, $requestedName, $options);

        self::assertInstanceOf(Logger::class, $logger);
        self::assertSame($timezone, $logger->getTimezone());
    }

    /**
     * @throws Exception
     */
    public function testInvoceWithHandlers(): void
    {
        $requestedName = Logger::class;
        $timezone      = new DateTimeZone('Europe/Berlin');
        $options       = ['name' => 'xyz', 'timezone' => $timezone, 'handlers' => 'fake'];

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new MonologFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('Handlers must be iterable');
        $this->expectExceptionCode(0);

        $factory($container, $requestedName, $options);
    }

    /**
     * @throws Exception
     */
    public function testInvoceWithHandlers2(): void
    {
        $requestedName = Logger::class;
        $timezone      = new DateTimeZone('Europe/Berlin');
        $options       = ['name' => 'xyz', 'timezone' => $timezone, 'handlers' => [[]]];

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologHandlerPluginManager::class)
            ->willThrowException(new ServiceNotFoundException());

        $factory = new MonologFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionMessage(sprintf('Could not find service %s', MonologHandlerPluginManager::class));
        $this->expectExceptionCode(0);

        $factory($container, $requestedName, $options);
    }

    /**
     * @throws Exception
     */
    public function testInvoceWithHandlers3(): void
    {
        $requestedName = Logger::class;
        $timezone      = new DateTimeZone('Europe/Berlin');
        $options       = ['name' => 'xyz', 'timezone' => $timezone, 'handlers' => [['enabled' => true]]];

        $monologHandlerPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::never())
            ->method('get');

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologHandlerPluginManager::class)
            ->willReturn($monologHandlerPluginManager);

        $factory = new MonologFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('Options must contain a name for the handler');
        $this->expectExceptionCode(0);

        $factory($container, $requestedName, $options);
    }

    /**
     * @throws Exception
     */
    public function testInvoceWithHandlers4(): void
    {
        $requestedName = Logger::class;
        $timezone      = new DateTimeZone('Europe/Berlin');
        $options       = [
            'name' => 'xyz',
            'timezone' => $timezone,
            'handlers' => [
                ['enabled' => false],
                [
                    'enabled' => true,
                    'name' => 'xyz',
                ],
                ['name' => 'abc'],
            ],
        ];

        $monologHandlerPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::once())
            ->method('get')
            ->with('xyz', ['enabled' => true, 'name' => 'xyz'])
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

        $factory = new MonologFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionMessage(sprintf('Could not find service %s', 'xyz'));
        $this->expectExceptionCode(0);

        $factory($container, $requestedName, $options);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testInvoceWithHandlers5(): void
    {
        $requestedName = Logger::class;
        $timezone      = new DateTimeZone('Europe/Berlin');
        $options       = [
            'name' => 'xyz',
            'timezone' => $timezone,
            'handlers' => [
                ['enabled' => false],
                [
                    'enabled' => true,
                    'name' => 'xyz',
                ],
                ['name' => 'abc'],
                $this->createMock(HandlerInterface::class),
            ],
        ];

        $handler = $this->createMock(HandlerInterface::class);

        $monologHandlerPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive(['xyz', ['enabled' => true, 'name' => 'xyz']], ['abc', ['name' => 'abc']])
            ->willReturn($handler);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologHandlerPluginManager::class)
            ->willReturn($monologHandlerPluginManager);

        $factory = new MonologFactory();

        $logger = $factory($container, $requestedName, $options);

        self::assertInstanceOf(Logger::class, $logger);
        self::assertSame($timezone, $logger->getTimezone());
        self::assertIsArray($logger->getHandlers());
        self::assertCount(4, $logger->getHandlers());
    }

    /**
     * @throws Exception
     */
    public function testInvoceWithProcessors(): void
    {
        $requestedName = Logger::class;
        $timezone      = new DateTimeZone('Europe/Berlin');
        $options       = ['name' => 'xyz', 'timezone' => $timezone, 'processors' => 'fake'];

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new MonologFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('Processors must be an Array');
        $this->expectExceptionCode(0);

        $factory($container, $requestedName, $options);
    }

    /**
     * @throws Exception
     */
    public function testInvoceWithProcessors2(): void
    {
        $requestedName = Logger::class;
        $timezone      = new DateTimeZone('Europe/Berlin');
        $options       = ['name' => 'xyz', 'timezone' => $timezone, 'processors' => [[]]];

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologProcessorPluginManager::class)
            ->willThrowException(new ServiceNotFoundException());

        $factory = new MonologFactory();

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
        $timezone      = new DateTimeZone('Europe/Berlin');
        $options       = ['name' => 'xyz', 'timezone' => $timezone, 'processors' => [['enabled' => true]]];

        $monologProcessorPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologProcessorPluginManager->expects(self::never())
            ->method('has');
        $monologProcessorPluginManager->expects(self::never())
            ->method('get');

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologProcessorPluginManager::class)
            ->willReturn($monologProcessorPluginManager);

        $factory = new MonologFactory();

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
        $timezone      = new DateTimeZone('Europe/Berlin');
        $options       = [
            'name' => 'xyz',
            'timezone' => $timezone,
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

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologProcessorPluginManager::class)
            ->willReturn($monologProcessorPluginManager);

        $factory = new MonologFactory();

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
        $timezone      = new DateTimeZone('Europe/Berlin');
        $options       = [
            'name' => 'xyz',
            'timezone' => $timezone,
            'processors' => [
                ['enabled' => false],
                [
                    'enabled' => true,
                    'name' => 'xyz',
                    'parameters' => ['efg' => 'ijk'],
                ],
                ['name' => 'abc'],
                static fn (array $record): array => $record,
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

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologProcessorPluginManager::class)
            ->willReturn($monologProcessorPluginManager);

        $factory = new MonologFactory();

        $logger = $factory($container, $requestedName, $options);

        self::assertInstanceOf(Logger::class, $logger);
        self::assertSame($timezone, $logger->getTimezone());
        self::assertIsArray($logger->getProcessors());
        self::assertCount(3, $logger->getProcessors());
    }
}
