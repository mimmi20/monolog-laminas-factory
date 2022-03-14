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
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Mimmi20\LoggerFactory\Handler\NativeMailerHandlerFactory;
use Mimmi20\LoggerFactory\MonologFormatterPluginManager;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\HtmlFormatter;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\NativeMailerHandler;
use Monolog\Logger;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

use function sprintf;

final class NativeMailerHandlerFactoryTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testInvokeWithoutConfig(): void
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
    public function testInvokeWithEmptyConfig(): void
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
    public function testInvokeWithConfig(): void
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
    public function testInvokeWithConfig2(): void
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
    public function testInvokeWithConfig3(): void
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

        self::assertInstanceOf(HtmlFormatter::class, $handler->getFormatter());

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
    public function testInvokeWithConfig4(): void
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

        self::assertInstanceOf(HtmlFormatter::class, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');
        $proc->setAccessible(true);

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     */
    public function testInvokeWithConfigAndBoolFormatter(): void
    {
        $to             = 'test-to';
        $subject        = 'test-subject';
        $from           = 'test-from';
        $maxColumnWidth = 120;
        $contentType    = 'test/fake';
        $encoding       = 'iso-42';
        $formatter      = true;

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
        $this->expectExceptionMessage(
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class)
        );

        $factory($container, '', ['to' => $to, 'subject' => $subject, 'from' => $from, 'level' => LogLevel::ALERT, 'bubble' => false, 'maxColumnWidth' => $maxColumnWidth, 'contentType' => $contentType, 'encoding' => $encoding, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     */
    public function testInvokeWithConfigAndFormatter(): void
    {
        $to             = 'test-to';
        $subject        = 'test-subject';
        $from           = 'test-from';
        $maxColumnWidth = 120;
        $contentType    = 'test/fake';
        $encoding       = 'iso-42';
        $formatter      = $this->getMockBuilder(LineFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologFormatterPluginManager::class)
            ->willThrowException(new ServiceNotFoundException());

        $factory = new NativeMailerHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologFormatterPluginManager::class)
        );

        $factory($container, '', ['to' => $to, 'subject' => $subject, 'from' => $from, 'level' => LogLevel::ALERT, 'bubble' => false, 'maxColumnWidth' => $maxColumnWidth, 'contentType' => $contentType, 'encoding' => $encoding, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvokeWithConfigAndFormatter2(): void
    {
        $to             = 'test-to';
        $subject        = 'test-subject';
        $from           = 'test-from';
        $maxColumnWidth = 120;
        $contentType    = 'test/fake';
        $encoding       = 'iso-42';
        $formatter      = $this->getMockBuilder(LineFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $monologFormatterPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologFormatterPluginManager->expects(self::never())
            ->method('has');
        $monologFormatterPluginManager->expects(self::never())
            ->method('get');

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologFormatterPluginManager::class)
            ->willReturn($monologFormatterPluginManager);

        $factory = new NativeMailerHandlerFactory();

        $handler = $factory($container, '', ['to' => $to, 'subject' => $subject, 'from' => $from, 'level' => LogLevel::ALERT, 'bubble' => false, 'maxColumnWidth' => $maxColumnWidth, 'contentType' => $contentType, 'encoding' => $encoding, 'formatter' => $formatter]);

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

        self::assertSame($formatter, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');
        $proc->setAccessible(true);

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     */
    public function testInvokeWithConfigAndBoolProcessors(): void
    {
        $to             = 'test-to';
        $subject        = 'test-subject';
        $from           = 'test-from';
        $maxColumnWidth = 120;
        $contentType    = 'test/fake';
        $encoding       = 'iso-42';
        $processors     = true;

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
        $this->expectExceptionMessage('Processors must be an Array');

        $factory($container, '', ['to' => $to, 'subject' => $subject, 'from' => $from, 'level' => LogLevel::ALERT, 'bubble' => false, 'maxColumnWidth' => $maxColumnWidth, 'contentType' => $contentType, 'encoding' => $encoding, 'processors' => $processors]);
    }
}
