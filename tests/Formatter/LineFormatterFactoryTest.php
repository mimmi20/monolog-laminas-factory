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
use Mimmi20\LoggerFactory\Formatter\LineFormatterFactory;
use Monolog\Formatter\LineFormatter;
use Monolog\Formatter\NormalizerFormatter;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionProperty;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

final class LineFormatterFactoryTest extends TestCase
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

        $factory = new LineFormatterFactory();

        $formatter = $factory($container, '');

        self::assertInstanceOf(LineFormatter::class, $formatter);
        self::assertSame(NormalizerFormatter::SIMPLE_DATE, $formatter->getDateFormat());

        $ailb = new ReflectionProperty($formatter, 'allowInlineLineBreaks');
        $ailb->setAccessible(true);

        self::assertFalse($ailb->getValue($formatter));

        $format = new ReflectionProperty($formatter, 'format');
        $format->setAccessible(true);

        self::assertSame(LineFormatter::SIMPLE_FORMAT, $format->getValue($formatter));

        $ig = new ReflectionProperty($formatter, 'ignoreEmptyContextAndExtra');
        $ig->setAccessible(true);

        self::assertFalse($ig->getValue($formatter));

        $st = new ReflectionProperty($formatter, 'includeStacktraces');
        $st->setAccessible(true);

        self::assertNull($st->getValue($formatter));
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

        $factory = new LineFormatterFactory();

        $formatter = $factory($container, '', []);

        self::assertInstanceOf(LineFormatter::class, $formatter);
        self::assertSame(NormalizerFormatter::SIMPLE_DATE, $formatter->getDateFormat());

        $ailb = new ReflectionProperty($formatter, 'allowInlineLineBreaks');
        $ailb->setAccessible(true);

        self::assertFalse($ailb->getValue($formatter));

        $format = new ReflectionProperty($formatter, 'format');
        $format->setAccessible(true);

        self::assertSame(LineFormatter::SIMPLE_FORMAT, $format->getValue($formatter));

        $ig = new ReflectionProperty($formatter, 'ignoreEmptyContextAndExtra');
        $ig->setAccessible(true);

        self::assertFalse($ig->getValue($formatter));

        $st = new ReflectionProperty($formatter, 'includeStacktraces');
        $st->setAccessible(true);

        self::assertNull($st->getValue($formatter));
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function testInvoceWithConfig(): void
    {
        $format                     = '[abc] [def]';
        $dateFormat                 = 'xxx__Y-m-d\TH:i:sP__xxx';
        $allowInlineLineBreaks      = true;
        $ignoreEmptyContextAndExtra = true;
        $include                    = true;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new LineFormatterFactory();

        $formatter = $factory($container, '', ['format' => $format, 'dateFormat' => $dateFormat, 'allowInlineLineBreaks' => $allowInlineLineBreaks, 'ignoreEmptyContextAndExtra' => $ignoreEmptyContextAndExtra, 'includeStacktraces' => $include]);

        self::assertInstanceOf(LineFormatter::class, $formatter);
        self::assertSame($dateFormat, $formatter->getDateFormat());

        $ailb = new ReflectionProperty($formatter, 'allowInlineLineBreaks');
        $ailb->setAccessible(true);

        self::assertTrue($ailb->getValue($formatter));

        $formatP = new ReflectionProperty($formatter, 'format');
        $formatP->setAccessible(true);

        self::assertSame($format, $formatP->getValue($formatter));

        $ig = new ReflectionProperty($formatter, 'ignoreEmptyContextAndExtra');
        $ig->setAccessible(true);

        self::assertTrue($ig->getValue($formatter));

        $st = new ReflectionProperty($formatter, 'includeStacktraces');
        $st->setAccessible(true);

        self::assertTrue($st->getValue($formatter));
    }
}