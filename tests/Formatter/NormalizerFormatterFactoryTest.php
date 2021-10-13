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
use Mimmi20\LoggerFactory\Formatter\NormalizerFormatterFactory;
use Monolog\Formatter\NormalizerFormatter;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionProperty;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

use const JSON_PRETTY_PRINT;

final class NormalizerFormatterFactoryTest extends TestCase
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

        $factory = new NormalizerFormatterFactory();

        $formatter = $factory($container, '');

        self::assertInstanceOf(NormalizerFormatter::class, $formatter);
        self::assertSame(NormalizerFormatter::SIMPLE_DATE, $formatter->getDateFormat());
        self::assertSame(9, $formatter->getMaxNormalizeDepth());
        self::assertSame(1000, $formatter->getMaxNormalizeItemCount());

        $jeo = new ReflectionProperty($formatter, 'jsonEncodeOptions');
        $jeo->setAccessible(true);

        $jsonEncodeOptions = $jeo->getValue($formatter);

        self::assertGreaterThanOrEqual(1, $jsonEncodeOptions & ~JSON_PRETTY_PRINT);
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

        $factory = new NormalizerFormatterFactory();

        $formatter = $factory($container, '', []);

        self::assertInstanceOf(NormalizerFormatter::class, $formatter);
        self::assertSame(NormalizerFormatter::SIMPLE_DATE, $formatter->getDateFormat());
        self::assertSame(9, $formatter->getMaxNormalizeDepth());
        self::assertSame(1000, $formatter->getMaxNormalizeItemCount());

        $jeo = new ReflectionProperty($formatter, 'jsonEncodeOptions');
        $jeo->setAccessible(true);

        $jsonEncodeOptions = $jeo->getValue($formatter);

        self::assertGreaterThanOrEqual(1, $jsonEncodeOptions & ~JSON_PRETTY_PRINT);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function testInvoceWithConfig(): void
    {
        $dateFormat            = 'xxx__Y-m-d\TH:i:sP__xxx';
        $maxNormalizeDepth     = 42;
        $maxNormalizeItemCount = 4711;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new NormalizerFormatterFactory();

        $formatter = $factory($container, '', ['dateFormat' => $dateFormat, 'maxNormalizeDepth' => $maxNormalizeDepth, 'maxNormalizeItemCount' => $maxNormalizeItemCount, 'prettyPrint' => true]);

        self::assertInstanceOf(NormalizerFormatter::class, $formatter);
        self::assertSame($dateFormat, $formatter->getDateFormat());
        self::assertSame($maxNormalizeDepth, $formatter->getMaxNormalizeDepth());
        self::assertSame($maxNormalizeItemCount, $formatter->getMaxNormalizeItemCount());

        $jeo = new ReflectionProperty($formatter, 'jsonEncodeOptions');
        $jeo->setAccessible(true);

        $jsonEncodeOptions = $jeo->getValue($formatter);

        self::assertGreaterThanOrEqual(1, $jsonEncodeOptions & ~JSON_PRETTY_PRINT);
    }
}
