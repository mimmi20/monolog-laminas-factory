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
use Mimmi20\LoggerFactory\Formatter\LogmaticFormatterFactory;
use Monolog\Formatter\JsonFormatter;
use Monolog\Formatter\LogmaticFormatter;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionProperty;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

final class LogmaticFormatterFactoryTest extends TestCase
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

        $factory = new LogmaticFormatterFactory();

        $formatter = $factory($container, '');

        self::assertInstanceOf(LogmaticFormatter::class, $formatter);
        self::assertSame(JsonFormatter::BATCH_MODE_JSON, $formatter->getBatchMode());
        self::assertTrue($formatter->isAppendingNewlines());

        $ig = new ReflectionProperty($formatter, 'ignoreEmptyContextAndExtra');
        $ig->setAccessible(true);

        self::assertFalse($ig->getValue($formatter));

        $st = new ReflectionProperty($formatter, 'includeStacktraces');
        $st->setAccessible(true);

        self::assertFalse($st->getValue($formatter));

        $h = new ReflectionProperty($formatter, 'hostname');
        $h->setAccessible(true);

        self::assertSame('', $h->getValue($formatter));

        $a = new ReflectionProperty($formatter, 'appname');
        $a->setAccessible(true);

        self::assertSame('', $a->getValue($formatter));
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

        $factory = new LogmaticFormatterFactory();

        $formatter = $factory($container, '', []);

        self::assertInstanceOf(LogmaticFormatter::class, $formatter);
        self::assertSame(JsonFormatter::BATCH_MODE_JSON, $formatter->getBatchMode());
        self::assertTrue($formatter->isAppendingNewlines());

        $ig = new ReflectionProperty($formatter, 'ignoreEmptyContextAndExtra');
        $ig->setAccessible(true);

        self::assertFalse($ig->getValue($formatter));

        $st = new ReflectionProperty($formatter, 'includeStacktraces');
        $st->setAccessible(true);

        self::assertFalse($st->getValue($formatter));

        $h = new ReflectionProperty($formatter, 'hostname');
        $h->setAccessible(true);

        self::assertSame('', $h->getValue($formatter));

        $a = new ReflectionProperty($formatter, 'appname');
        $a->setAccessible(true);

        self::assertSame('', $a->getValue($formatter));
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function testInvoceWithConfig(): void
    {
        $batchMode     = JsonFormatter::BATCH_MODE_NEWLINES;
        $appendNewline = false;
        $include       = true;
        $hostname      = 'abc';
        $appname       = 'xyz';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new LogmaticFormatterFactory();

        $formatter = $factory($container, '', ['batchMode' => $batchMode, 'appendNewline' => $appendNewline, 'includeStacktraces' => $include, 'hostname' => $hostname, 'appName' => $appname]);

        self::assertInstanceOf(LogmaticFormatter::class, $formatter);
        self::assertSame($batchMode, $formatter->getBatchMode());
        self::assertFalse($formatter->isAppendingNewlines());

        $ig = new ReflectionProperty($formatter, 'ignoreEmptyContextAndExtra');
        $ig->setAccessible(true);

        self::assertFalse($ig->getValue($formatter));

        $st = new ReflectionProperty($formatter, 'includeStacktraces');
        $st->setAccessible(true);

        self::assertTrue($st->getValue($formatter));

        $h = new ReflectionProperty($formatter, 'hostname');
        $h->setAccessible(true);

        self::assertSame($hostname, $h->getValue($formatter));

        $a = new ReflectionProperty($formatter, 'appname');
        $a->setAccessible(true);

        self::assertSame($appname, $a->getValue($formatter));
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function testInvoceWithConfig2(): void
    {
        $batchMode     = JsonFormatter::BATCH_MODE_JSON;
        $appendNewline = false;
        $include       = true;
        $hostname      = 'abc';
        $appname       = 'xyz';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new LogmaticFormatterFactory();

        $formatter = $factory($container, '', ['batchMode' => $batchMode, 'appendNewline' => $appendNewline, 'includeStacktraces' => $include, 'hostname' => $hostname, 'appName' => $appname]);

        self::assertInstanceOf(LogmaticFormatter::class, $formatter);
        self::assertSame($batchMode, $formatter->getBatchMode());
        self::assertFalse($formatter->isAppendingNewlines());

        $ig = new ReflectionProperty($formatter, 'ignoreEmptyContextAndExtra');
        $ig->setAccessible(true);

        self::assertFalse($ig->getValue($formatter));

        $st = new ReflectionProperty($formatter, 'includeStacktraces');
        $st->setAccessible(true);

        self::assertTrue($st->getValue($formatter));

        $h = new ReflectionProperty($formatter, 'hostname');
        $h->setAccessible(true);

        self::assertSame($hostname, $h->getValue($formatter));

        $a = new ReflectionProperty($formatter, 'appname');
        $a->setAccessible(true);

        self::assertSame($appname, $a->getValue($formatter));
    }
}
