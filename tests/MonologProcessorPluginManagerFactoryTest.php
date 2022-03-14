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

use Interop\Container\ContainerInterface;
use Laminas\Log\Processor\PsrPlaceholder;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Mimmi20\LoggerFactory\MonologProcessorPluginManager;
use Mimmi20\LoggerFactory\MonologProcessorPluginManagerFactory;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

use function sprintf;

final class MonologProcessorPluginManagerFactoryTest extends TestCase
{
    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testInvoke1(): void
    {
        $requestedName = PsrPlaceholder::class;
        $options       = ['abc' => 'xyz'];

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::once())
            ->method('has')
            ->with('ServiceListener')
            ->willReturn(true);
        $container->expects(self::never())
            ->method('get');

        $factory = new MonologProcessorPluginManagerFactory();

        self::assertInstanceOf(MonologProcessorPluginManager::class, $factory($container, $requestedName, $options));
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testInvoke2(): void
    {
        $requestedName = PsrPlaceholder::class;
        $options       = ['abc' => 'xyz'];

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::exactly(2))
            ->method('has')
            ->withConsecutive(['ServiceListener'], ['config'])
            ->willReturnOnConsecutiveCalls(false, false);
        $container->expects(self::never())
            ->method('get');

        $factory = new MonologProcessorPluginManagerFactory();

        self::assertInstanceOf(MonologProcessorPluginManager::class, $factory($container, $requestedName, $options));
    }

    /**
     * @throws Exception
     */
    public function testInvoke3(): void
    {
        $requestedName = PsrPlaceholder::class;
        $options       = ['abc' => 'xyz'];

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::exactly(2))
            ->method('has')
            ->withConsecutive(['ServiceListener'], ['config'])
            ->willReturnOnConsecutiveCalls(false, true);
        $container->expects(self::once())
            ->method('get')
            ->with('config')
            ->willThrowException(new ServiceNotFoundException());

        $factory = new MonologProcessorPluginManagerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionMessage(sprintf('Could not find service %s', 'config'));
        $this->expectExceptionCode(0);

        $factory($container, $requestedName, $options);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testInvoke4(): void
    {
        $requestedName = PsrPlaceholder::class;
        $options       = ['abc' => 'xyz'];
        $config        = [];

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::exactly(2))
            ->method('has')
            ->withConsecutive(['ServiceListener'], ['config'])
            ->willReturnOnConsecutiveCalls(false, true);
        $container->expects(self::once())
            ->method('get')
            ->with('config')
            ->willReturn($config);

        $factory = new MonologProcessorPluginManagerFactory();

        self::assertInstanceOf(MonologProcessorPluginManager::class, $factory($container, $requestedName, $options));
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testInvoke5(): void
    {
        $requestedName = PsrPlaceholder::class;
        $options       = ['abc' => 'xyz'];
        $config        = ['monolog_processors' => 'test'];

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::exactly(2))
            ->method('has')
            ->withConsecutive(['ServiceListener'], ['config'])
            ->willReturnOnConsecutiveCalls(false, true);
        $container->expects(self::once())
            ->method('get')
            ->with('config')
            ->willReturn($config);

        $factory = new MonologProcessorPluginManagerFactory();

        self::assertInstanceOf(MonologProcessorPluginManager::class, $factory($container, $requestedName, $options));
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testInvoke6(): void
    {
        $requestedName = PsrPlaceholder::class;
        $options       = ['abc' => 'xyz'];
        $config        = ['monolog_processors' => []];

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::exactly(2))
            ->method('has')
            ->withConsecutive(['ServiceListener'], ['config'])
            ->willReturnOnConsecutiveCalls(false, true);
        $container->expects(self::once())
            ->method('get')
            ->with('config')
            ->willReturn($config);

        $factory = new MonologProcessorPluginManagerFactory();

        self::assertInstanceOf(MonologProcessorPluginManager::class, $factory($container, $requestedName, $options));
    }
}
