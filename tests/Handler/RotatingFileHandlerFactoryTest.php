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
use Mimmi20\LoggerFactory\Handler\RotatingFileHandlerFactory;
use Mimmi20\LoggerFactory\MonologFormatterPluginManager;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

use function date;
use function sprintf;

final class RotatingFileHandlerFactoryTest extends TestCase
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

        $factory = new RotatingFileHandlerFactory();

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

        $factory = new RotatingFileHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No filename provided');

        $factory($container, '', []);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvoceWithConfig(): void
    {
        $filename = '/tmp/test-file';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new RotatingFileHandlerFactory();

        $handler = $factory($container, '', ['filename' => $filename]);

        self::assertInstanceOf(RotatingFileHandler::class, $handler);

        self::assertNull($handler->getStream());
        self::assertSame($filename . '-' . date(RotatingFileHandler::FILE_PER_DAY), $handler->getUrl());
        self::assertSame(Logger::DEBUG, $handler->getLevel());
        self::assertTrue($handler->getBubble());

        $fn = new ReflectionProperty($handler, 'filename');
        $fn->setAccessible(true);

        self::assertSame($filename, $fn->getValue($handler));

        $mf = new ReflectionProperty($handler, 'maxFiles');
        $mf->setAccessible(true);

        self::assertSame(0, $mf->getValue($handler));

        $fp = new ReflectionProperty($handler, 'filePermission');
        $fp->setAccessible(true);

        self::assertNull($fp->getValue($handler));

        $ul = new ReflectionProperty($handler, 'useLocking');
        $ul->setAccessible(true);

        self::assertFalse($ul->getValue($handler));

        self::assertInstanceOf(LineFormatter::class, $handler->getFormatter());

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
    public function testInvoceWithConfig2(): void
    {
        $filename       = '/tmp/test-file';
        $filenameFormat = '{filename}_{date}';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new RotatingFileHandlerFactory();

        $handler = $factory($container, '', ['filename' => $filename, 'filenameFormat' => $filenameFormat]);

        self::assertInstanceOf(RotatingFileHandler::class, $handler);

        self::assertNull($handler->getStream());
        self::assertSame($filename . '_' . date(RotatingFileHandler::FILE_PER_DAY), $handler->getUrl());
        self::assertSame(Logger::DEBUG, $handler->getLevel());
        self::assertTrue($handler->getBubble());

        $fn = new ReflectionProperty($handler, 'filename');
        $fn->setAccessible(true);

        self::assertSame($filename, $fn->getValue($handler));

        $mf = new ReflectionProperty($handler, 'maxFiles');
        $mf->setAccessible(true);

        self::assertSame(0, $mf->getValue($handler));

        $fp = new ReflectionProperty($handler, 'filePermission');
        $fp->setAccessible(true);

        self::assertNull($fp->getValue($handler));

        $ul = new ReflectionProperty($handler, 'useLocking');
        $ul->setAccessible(true);

        self::assertFalse($ul->getValue($handler));

        self::assertInstanceOf(LineFormatter::class, $handler->getFormatter());

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
    public function testInvoceWithConfig3(): void
    {
        $filename   = '/tmp/test-file';
        $dateFormat = RotatingFileHandler::FILE_PER_MONTH;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new RotatingFileHandlerFactory();

        $handler = $factory($container, '', ['filename' => $filename, 'dateFormat' => $dateFormat]);

        self::assertInstanceOf(RotatingFileHandler::class, $handler);

        self::assertNull($handler->getStream());
        self::assertSame($filename . '-' . date($dateFormat), $handler->getUrl());
        self::assertSame(Logger::DEBUG, $handler->getLevel());
        self::assertTrue($handler->getBubble());

        $fn = new ReflectionProperty($handler, 'filename');
        $fn->setAccessible(true);

        self::assertSame($filename, $fn->getValue($handler));

        $mf = new ReflectionProperty($handler, 'maxFiles');
        $mf->setAccessible(true);

        self::assertSame(0, $mf->getValue($handler));

        $fp = new ReflectionProperty($handler, 'filePermission');
        $fp->setAccessible(true);

        self::assertNull($fp->getValue($handler));

        $ul = new ReflectionProperty($handler, 'useLocking');
        $ul->setAccessible(true);

        self::assertFalse($ul->getValue($handler));

        self::assertInstanceOf(LineFormatter::class, $handler->getFormatter());

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
    public function testInvoceWithConfig4(): void
    {
        $filename       = '/tmp/test-file';
        $maxFiles       = 99;
        $level          = LogLevel::ALERT;
        $bubble         = false;
        $filePermission = 0755;
        $useLocking     = false;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new RotatingFileHandlerFactory();

        $handler = $factory($container, '', ['filename' => $filename, 'maxFiles' => $maxFiles, 'level' => $level, 'bubble' => $bubble, 'filePermission' => $filePermission, 'useLocking' => $useLocking]);

        self::assertInstanceOf(RotatingFileHandler::class, $handler);

        self::assertNull($handler->getStream());
        self::assertSame($filename . '-' . date(RotatingFileHandler::FILE_PER_DAY), $handler->getUrl());
        self::assertSame(Logger::ALERT, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $fn = new ReflectionProperty($handler, 'filename');
        $fn->setAccessible(true);

        self::assertSame($filename, $fn->getValue($handler));

        $mf = new ReflectionProperty($handler, 'maxFiles');
        $mf->setAccessible(true);

        self::assertSame($maxFiles, $mf->getValue($handler));

        $fp = new ReflectionProperty($handler, 'filePermission');
        $fp->setAccessible(true);

        self::assertSame($filePermission, $fp->getValue($handler));

        $ul = new ReflectionProperty($handler, 'useLocking');
        $ul->setAccessible(true);

        self::assertFalse($ul->getValue($handler));

        self::assertInstanceOf(LineFormatter::class, $handler->getFormatter());

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
    public function testInvoceWithConfig5(): void
    {
        $filename       = '/tmp/test-file';
        $maxFiles       = 99;
        $level          = LogLevel::ALERT;
        $bubble         = false;
        $filePermission = 0755;
        $useLocking     = false;
        $filenameFormat = '{filename}_{date}';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new RotatingFileHandlerFactory();

        $handler = $factory($container, '', ['filename' => $filename, 'maxFiles' => $maxFiles, 'level' => $level, 'bubble' => $bubble, 'filePermission' => $filePermission, 'useLocking' => $useLocking, 'filenameFormat' => $filenameFormat]);

        self::assertInstanceOf(RotatingFileHandler::class, $handler);

        self::assertNull($handler->getStream());
        self::assertSame($filename . '_' . date(RotatingFileHandler::FILE_PER_DAY), $handler->getUrl());
        self::assertSame(Logger::ALERT, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $fn = new ReflectionProperty($handler, 'filename');
        $fn->setAccessible(true);

        self::assertSame($filename, $fn->getValue($handler));

        $mf = new ReflectionProperty($handler, 'maxFiles');
        $mf->setAccessible(true);

        self::assertSame($maxFiles, $mf->getValue($handler));

        $fp = new ReflectionProperty($handler, 'filePermission');
        $fp->setAccessible(true);

        self::assertSame($filePermission, $fp->getValue($handler));

        $ul = new ReflectionProperty($handler, 'useLocking');
        $ul->setAccessible(true);

        self::assertFalse($ul->getValue($handler));

        self::assertInstanceOf(LineFormatter::class, $handler->getFormatter());

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
    public function testInvoceWithConfig6(): void
    {
        $filename       = '/tmp/test-file';
        $maxFiles       = 99;
        $level          = LogLevel::ALERT;
        $bubble         = false;
        $filePermission = 0755;
        $useLocking     = false;
        $dateFormat     = RotatingFileHandler::FILE_PER_MONTH;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new RotatingFileHandlerFactory();

        $handler = $factory($container, '', ['filename' => $filename, 'maxFiles' => $maxFiles, 'level' => $level, 'bubble' => $bubble, 'filePermission' => $filePermission, 'useLocking' => $useLocking, 'dateFormat' => $dateFormat]);

        self::assertInstanceOf(RotatingFileHandler::class, $handler);

        self::assertNull($handler->getStream());
        self::assertSame($filename . '-' . date($dateFormat), $handler->getUrl());
        self::assertSame(Logger::ALERT, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $fn = new ReflectionProperty($handler, 'filename');
        $fn->setAccessible(true);

        self::assertSame($filename, $fn->getValue($handler));

        $mf = new ReflectionProperty($handler, 'maxFiles');
        $mf->setAccessible(true);

        self::assertSame($maxFiles, $mf->getValue($handler));

        $fp = new ReflectionProperty($handler, 'filePermission');
        $fp->setAccessible(true);

        self::assertSame($filePermission, $fp->getValue($handler));

        $ul = new ReflectionProperty($handler, 'useLocking');
        $ul->setAccessible(true);

        self::assertFalse($ul->getValue($handler));

        self::assertInstanceOf(LineFormatter::class, $handler->getFormatter());

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
        $filename       = '/tmp/test-file';
        $maxFiles       = 99;
        $level          = LogLevel::ALERT;
        $bubble         = false;
        $filePermission = 0755;
        $useLocking     = false;
        $dateFormat     = RotatingFileHandler::FILE_PER_MONTH;
        $formatter      = true;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new RotatingFileHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class)
        );

        $factory($container, '', ['filename' => $filename, 'maxFiles' => $maxFiles, 'level' => $level, 'bubble' => $bubble, 'filePermission' => $filePermission, 'useLocking' => $useLocking, 'dateFormat' => $dateFormat, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     */
    public function testInvoceWithConfigAndFormatter(): void
    {
        $filename       = '/tmp/test-file';
        $maxFiles       = 99;
        $level          = LogLevel::ALERT;
        $bubble         = false;
        $filePermission = 0755;
        $useLocking     = false;
        $dateFormat     = RotatingFileHandler::FILE_PER_MONTH;
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

        $factory = new RotatingFileHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologFormatterPluginManager::class)
        );

        $factory($container, '', ['filename' => $filename, 'maxFiles' => $maxFiles, 'level' => $level, 'bubble' => $bubble, 'filePermission' => $filePermission, 'useLocking' => $useLocking, 'dateFormat' => $dateFormat, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvoceWithConfigAndFormatter2(): void
    {
        $filename       = '/tmp/test-file';
        $maxFiles       = 99;
        $level          = LogLevel::ALERT;
        $bubble         = false;
        $filePermission = 0755;
        $useLocking     = false;
        $dateFormat     = RotatingFileHandler::FILE_PER_MONTH;
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

        $factory = new RotatingFileHandlerFactory();

        $handler = $factory($container, '', ['filename' => $filename, 'maxFiles' => $maxFiles, 'level' => $level, 'bubble' => $bubble, 'filePermission' => $filePermission, 'useLocking' => $useLocking, 'dateFormat' => $dateFormat, 'formatter' => $formatter]);

        self::assertInstanceOf(RotatingFileHandler::class, $handler);

        self::assertNull($handler->getStream());
        self::assertSame($filename . '-' . date($dateFormat), $handler->getUrl());
        self::assertSame(Logger::ALERT, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $fn = new ReflectionProperty($handler, 'filename');
        $fn->setAccessible(true);

        self::assertSame($filename, $fn->getValue($handler));

        $mf = new ReflectionProperty($handler, 'maxFiles');
        $mf->setAccessible(true);

        self::assertSame($maxFiles, $mf->getValue($handler));

        $fp = new ReflectionProperty($handler, 'filePermission');
        $fp->setAccessible(true);

        self::assertSame($filePermission, $fp->getValue($handler));

        $ul = new ReflectionProperty($handler, 'useLocking');
        $ul->setAccessible(true);

        self::assertFalse($ul->getValue($handler));

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
    public function testInvoceWithConfigAndBoolProcessors(): void
    {
        $filename       = '/tmp/test-file';
        $maxFiles       = 99;
        $level          = LogLevel::ALERT;
        $bubble         = false;
        $filePermission = 0755;
        $useLocking     = false;
        $dateFormat     = RotatingFileHandler::FILE_PER_MONTH;
        $processors     = true;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new RotatingFileHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Processors must be an Array');

        $factory($container, '', ['filename' => $filename, 'maxFiles' => $maxFiles, 'level' => $level, 'bubble' => $bubble, 'filePermission' => $filePermission, 'useLocking' => $useLocking, 'dateFormat' => $dateFormat, 'processors' => $processors]);
    }
}
