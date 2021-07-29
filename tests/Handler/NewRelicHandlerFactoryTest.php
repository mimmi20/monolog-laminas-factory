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
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Mimmi20\LoggerFactory\Handler\NewRelicHandlerFactory;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\NormalizerFormatter;
use Monolog\Handler\NewRelicHandler;
use Monolog\Logger;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

use function sprintf;

final class NewRelicHandlerFactoryTest extends TestCase
{
    /**
     * @throws Exception
     * @throws ReflectionException
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

        $factory = new NewRelicHandlerFactory();

        $handler = $factory($container, '');

        self::assertInstanceOf(NewRelicHandler::class, $handler);

        self::assertSame(Logger::DEBUG, $handler->getLevel());
        self::assertTrue($handler->getBubble());

        $an = new ReflectionProperty($handler, 'appName');
        $an->setAccessible(true);

        self::assertNull($an->getValue($handler));

        $ea = new ReflectionProperty($handler, 'explodeArrays');
        $ea->setAccessible(true);

        self::assertFalse($ea->getValue($handler));

        $tn = new ReflectionProperty($handler, 'transactionName');
        $tn->setAccessible(true);

        self::assertNull($tn->getValue($handler));

        self::assertInstanceOf(NormalizerFormatter::class, $handler->getFormatter());

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
    public function testInvoceWithEmptyConfig(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new NewRelicHandlerFactory();

        $handler = $factory($container, '', []);

        self::assertInstanceOf(NewRelicHandler::class, $handler);

        self::assertSame(Logger::DEBUG, $handler->getLevel());
        self::assertTrue($handler->getBubble());

        $an = new ReflectionProperty($handler, 'appName');
        $an->setAccessible(true);

        self::assertNull($an->getValue($handler));

        $ea = new ReflectionProperty($handler, 'explodeArrays');
        $ea->setAccessible(true);

        self::assertFalse($ea->getValue($handler));

        $tn = new ReflectionProperty($handler, 'transactionName');
        $tn->setAccessible(true);

        self::assertNull($tn->getValue($handler));

        self::assertInstanceOf(NormalizerFormatter::class, $handler->getFormatter());

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
    public function testInvoceWithConfig(): void
    {
        $appName         = 'test-app';
        $transactionName = 'test-transaction';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new NewRelicHandlerFactory();

        $handler = $factory($container, '', ['level' => LogLevel::ALERT, 'bubble' => false, 'appName' => $appName, 'explodeArrays' => true, 'transactionName' => $transactionName]);

        self::assertInstanceOf(NewRelicHandler::class, $handler);

        self::assertSame(Logger::ALERT, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $an = new ReflectionProperty($handler, 'appName');
        $an->setAccessible(true);

        self::assertSame($appName, $an->getValue($handler));

        $ea = new ReflectionProperty($handler, 'explodeArrays');
        $ea->setAccessible(true);

        self::assertTrue($ea->getValue($handler));

        $tn = new ReflectionProperty($handler, 'transactionName');
        $tn->setAccessible(true);

        self::assertSame($transactionName, $tn->getValue($handler));

        self::assertInstanceOf(NormalizerFormatter::class, $handler->getFormatter());

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
        $appName         = 'test-app';
        $transactionName = 'test-transaction';
        $formatter       = true;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new NewRelicHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class)
        );

        $factory($container, '', ['level' => LogLevel::ALERT, 'bubble' => false, 'appName' => $appName, 'explodeArrays' => true, 'transactionName' => $transactionName, 'formatter' => $formatter]);
    }
}
