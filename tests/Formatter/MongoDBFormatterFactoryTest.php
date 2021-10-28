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

namespace Mimmi20Test\LoggerFactory\Formatter;

use Interop\Container\ContainerInterface;
use Mimmi20\LoggerFactory\Formatter\MongoDBFormatterFactory;
use Monolog\Formatter\MongoDBFormatter;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionProperty;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

final class MongoDBFormatterFactoryTest extends TestCase
{
    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ReflectionException
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

        $factory = new MongoDBFormatterFactory();

        $formatter = $factory($container, '');

        self::assertInstanceOf(MongoDBFormatter::class, $formatter);

        $mnl = new ReflectionProperty($formatter, 'maxNestingLevel');
        $mnl->setAccessible(true);

        self::assertSame(MongoDBFormatterFactory::DEFAULT_NESTING_LEVEL, $mnl->getValue($formatter));

        $ts = new ReflectionProperty($formatter, 'exceptionTraceAsString');
        $ts->setAccessible(true);

        self::assertTrue($ts->getValue($formatter));
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ReflectionException
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

        $factory = new MongoDBFormatterFactory();

        $formatter = $factory($container, '', []);

        self::assertInstanceOf(MongoDBFormatter::class, $formatter);

        $mnl = new ReflectionProperty($formatter, 'maxNestingLevel');
        $mnl->setAccessible(true);

        self::assertSame(MongoDBFormatterFactory::DEFAULT_NESTING_LEVEL, $mnl->getValue($formatter));

        $ts = new ReflectionProperty($formatter, 'exceptionTraceAsString');
        $ts->setAccessible(true);

        self::assertTrue($ts->getValue($formatter));
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function testInvoceWithConfig(): void
    {
        $maxNestingLevel        = 42;
        $exceptionTraceAsString = false;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new MongoDBFormatterFactory();

        $formatter = $factory($container, '', ['maxNestingLevel' => $maxNestingLevel, 'exceptionTraceAsString' => $exceptionTraceAsString]);

        self::assertInstanceOf(MongoDBFormatter::class, $formatter);

        $mnl = new ReflectionProperty($formatter, 'maxNestingLevel');
        $mnl->setAccessible(true);

        self::assertSame($maxNestingLevel, $mnl->getValue($formatter));

        $ts = new ReflectionProperty($formatter, 'exceptionTraceAsString');
        $ts->setAccessible(true);

        self::assertFalse($ts->getValue($formatter));
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function testInvoceWithConfig2(): void
    {
        $maxNestingLevel        = -42;
        $exceptionTraceAsString = true;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new MongoDBFormatterFactory();

        $formatter = $factory($container, '', ['maxNestingLevel' => $maxNestingLevel, 'exceptionTraceAsString' => $exceptionTraceAsString]);

        self::assertInstanceOf(MongoDBFormatter::class, $formatter);

        $mnl = new ReflectionProperty($formatter, 'maxNestingLevel');
        $mnl->setAccessible(true);

        self::assertSame(0, $mnl->getValue($formatter));

        $ts = new ReflectionProperty($formatter, 'exceptionTraceAsString');
        $ts->setAccessible(true);

        self::assertTrue($ts->getValue($formatter));
    }
}
