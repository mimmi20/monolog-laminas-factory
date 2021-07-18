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
use Mimmi20\LoggerFactory\Formatter\GelfMessageFormatterFactory;
use Monolog\Formatter\GelfMessageFormatter;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionProperty;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

use function gethostname;

final class GelfMessageFormatterFactoryTest extends TestCase
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

        $factory = new GelfMessageFormatterFactory();

        $formatter = $factory($container, '');

        self::assertInstanceOf(GelfMessageFormatter::class, $formatter);

        $s = new ReflectionProperty($formatter, 'systemName');
        $s->setAccessible(true);

        self::assertSame((string) gethostname(), $s->getValue($formatter));

        $ep = new ReflectionProperty($formatter, 'extraPrefix');
        $ep->setAccessible(true);

        self::assertSame('', $ep->getValue($formatter));

        $cp = new ReflectionProperty($formatter, 'contextPrefix');
        $cp->setAccessible(true);

        self::assertSame('ctxt_', $cp->getValue($formatter));

        $ml = new ReflectionProperty($formatter, 'maxLength');
        $ml->setAccessible(true);

        self::assertSame(32766, $ml->getValue($formatter));
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

        $factory = new GelfMessageFormatterFactory();

        $formatter = $factory($container, '', []);

        self::assertInstanceOf(GelfMessageFormatter::class, $formatter);

        $s = new ReflectionProperty($formatter, 'systemName');
        $s->setAccessible(true);

        self::assertSame((string) gethostname(), $s->getValue($formatter));

        $ep = new ReflectionProperty($formatter, 'extraPrefix');
        $ep->setAccessible(true);

        self::assertSame('', $ep->getValue($formatter));

        $cp = new ReflectionProperty($formatter, 'contextPrefix');
        $cp->setAccessible(true);

        self::assertSame('ctxt_', $cp->getValue($formatter));

        $ml = new ReflectionProperty($formatter, 'maxLength');
        $ml->setAccessible(true);

        self::assertSame(32766, $ml->getValue($formatter));
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function testInvoceWithConfig(): void
    {
        $systemName    = 'abc';
        $extraPrefix   = '__xxx';
        $contextPrefix = 'xyz';
        $maxLength     = 42;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new GelfMessageFormatterFactory();

        $formatter = $factory($container, '', ['systemName' => $systemName, 'extraPrefix' => $extraPrefix, 'contextPrefix' => $contextPrefix, 'maxLength' => $maxLength]);

        self::assertInstanceOf(GelfMessageFormatter::class, $formatter);

        $s = new ReflectionProperty($formatter, 'systemName');
        $s->setAccessible(true);

        self::assertSame($systemName, $s->getValue($formatter));

        $ep = new ReflectionProperty($formatter, 'extraPrefix');
        $ep->setAccessible(true);

        self::assertSame($extraPrefix, $ep->getValue($formatter));

        $cp = new ReflectionProperty($formatter, 'contextPrefix');
        $cp->setAccessible(true);

        self::assertSame($contextPrefix, $cp->getValue($formatter));

        $ml = new ReflectionProperty($formatter, 'maxLength');
        $ml->setAccessible(true);

        self::assertSame($maxLength, $ml->getValue($formatter));
    }
}
