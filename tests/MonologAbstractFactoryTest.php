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
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Mimmi20\LoggerFactory\MonologAbstractFactory;
use Monolog\Handler\StreamHandler;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

use function sprintf;

final class MonologAbstractFactoryTest extends TestCase
{
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

        $factory = new MonologAbstractFactory();

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
        $options       = ['abc' => 'xyz'];

        $handler = $this->createMock(StreamHandler::class);

        $cascader = $this->getMockBuilder(Cascader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cascader->expects(self::once())
            ->method('create')
            ->with($requestedName, $options)
            ->willReturn($handler);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::once())
            ->method('get')
            ->with(Cascader::class)
            ->willReturn($cascader);

        $factory = new MonologAbstractFactory();

        self::assertSame($handler, $factory($container, $requestedName, $options));
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testCanCreate(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $factory   = new MonologAbstractFactory();

        self::assertFalse($factory->canCreate($container, 'DoesNotExist'));
        self::assertTrue($factory->canCreate($container, StreamHandler::class));
    }
}
