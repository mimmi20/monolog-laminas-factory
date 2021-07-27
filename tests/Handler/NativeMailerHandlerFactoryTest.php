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
use Mimmi20\LoggerFactory\Handler\NativeMailerHandlerFactory;
use Monolog\Handler\NativeMailerHandler;
use Monolog\Logger;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

final class NativeMailerHandlerFactoryTest extends TestCase
{
    /**
     * @throws Exception
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

        $factory = new NativeMailerHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Options must be an Array');

        $factory($container, '');
    }

    /**
     * @throws Exception
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

        $factory = new NativeMailerHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('The required to is missing');

        $factory($container, '', []);
    }

    /**
     * @throws Exception
     */
    public function testInvoceWithConfig(): void
    {
        $to = 'test-to';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new NativeMailerHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('The required subject is missing');

        $factory($container, '', ['to' => $to]);
    }

    /**
     * @throws Exception
     */
    public function testInvoceWithConfig2(): void
    {
        $to      = 'test-to';
        $subject = 'test-subject';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new NativeMailerHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('The required from is missing');

        $factory($container, '', ['to' => $to, 'subject' => $subject]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvoceWithConfig3(): void
    {
        $to      = 'test-to';
        $subject = 'test-subject';
        $from    = 'test-from';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new NativeMailerHandlerFactory();

        $handler = $factory($container, '', ['to' => $to, 'subject' => $subject, 'from' => $from]);

        self::assertInstanceOf(NativeMailerHandler::class, $handler);

        self::assertSame(Logger::DEBUG, $handler->getLevel());
        self::assertTrue($handler->getBubble());
        self::assertNull($handler->getContentType());
        self::assertSame('utf-8', $handler->getEncoding());

        $toP = new ReflectionProperty($handler, 'to');
        $toP->setAccessible(true);

        self::assertSame([$to], $toP->getValue($handler));

        $subjectP = new ReflectionProperty($handler, 'subject');
        $subjectP->setAccessible(true);

        self::assertSame($subject, $subjectP->getValue($handler));

        $mcw = new ReflectionProperty($handler, 'maxColumnWidth');
        $mcw->setAccessible(true);

        self::assertSame(70, $mcw->getValue($handler));

        $headersP = new ReflectionProperty($handler, 'headers');
        $headersP->setAccessible(true);

        self::assertSame(['From: ' . $from], $headersP->getValue($handler));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvoceWithConfig4(): void
    {
        $to             = 'test-to';
        $subject        = 'test-subject';
        $from           = 'test-from';
        $maxColumnWidth = 120;
        $contentType    = 'test/fake';
        $encoding       = 'iso-42';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new NativeMailerHandlerFactory();

        $handler = $factory($container, '', ['to' => $to, 'subject' => $subject, 'from' => $from, 'level' => LogLevel::ALERT, 'bubble' => false, 'maxColumnWidth' => $maxColumnWidth, 'contentType' => $contentType, 'encoding' => $encoding]);

        self::assertInstanceOf(NativeMailerHandler::class, $handler);

        self::assertSame(Logger::ALERT, $handler->getLevel());
        self::assertFalse($handler->getBubble());
        self::assertSame($contentType, $handler->getContentType());
        self::assertSame($encoding, $handler->getEncoding());

        $toP = new ReflectionProperty($handler, 'to');
        $toP->setAccessible(true);

        self::assertSame([$to], $toP->getValue($handler));

        $subjectP = new ReflectionProperty($handler, 'subject');
        $subjectP->setAccessible(true);

        self::assertSame($subject, $subjectP->getValue($handler));

        $mcw = new ReflectionProperty($handler, 'maxColumnWidth');
        $mcw->setAccessible(true);

        self::assertSame($maxColumnWidth, $mcw->getValue($handler));

        $headersP = new ReflectionProperty($handler, 'headers');
        $headersP->setAccessible(true);

        self::assertSame(['From: ' . $from], $headersP->getValue($handler));
    }
}
