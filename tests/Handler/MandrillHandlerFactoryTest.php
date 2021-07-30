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
use Mimmi20\LoggerFactory\Handler\MandrillHandlerFactory;
use Mimmi20\LoggerFactory\MonologFormatterPluginManager;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\HtmlFormatter;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\MandrillHandler;
use Monolog\Logger;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;
use SebastianBergmann\RecursionContext\InvalidArgumentException;
use Swift_Message;

use function sprintf;

final class MandrillHandlerFactoryTest extends TestCase
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

        $factory = new MandrillHandlerFactory();

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

        $factory = new MandrillHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No apiKey provided');

        $factory($container, '', []);
    }

    /**
     * @throws Exception
     */
    public function testInvoceWithConfig(): void
    {
        $apiKey = 'test-key';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new MandrillHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No message service name or callback provided');

        $factory($container, '', ['apiKey' => $apiKey]);
    }

    /**
     * @throws Exception
     */
    public function testInvoceWithConfig2(): void
    {
        $apiKey  = 'test-key';
        $message = 'test-message';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::once())
            ->method('has')
            ->with($message)
            ->willReturn(false);
        $container->expects(self::never())
            ->method('get');

        $factory = new MandrillHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No Message service found');

        $factory($container, '', ['apiKey' => $apiKey, 'message' => $message]);
    }

    /**
     * @throws Exception
     */
    public function testInvoceWithConfig3(): void
    {
        $apiKey  = 'test-key';
        $message = 'test-message';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::once())
            ->method('has')
            ->with($message)
            ->willReturn(true);
        $container->expects(self::once())
            ->method('get')
            ->with($message)
            ->willThrowException(new ServiceNotFoundException());

        $factory = new MandrillHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not load service %s', $message));

        $factory($container, '', ['apiKey' => $apiKey, 'message' => $message]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvoceWithConfig4(): void
    {
        $apiKey      = 'test-key';
        $messageName = 'test-message';
        $message     = $this->getMockBuilder(Swift_Message::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::once())
            ->method('has')
            ->with($messageName)
            ->willReturn(true);
        $container->expects(self::once())
            ->method('get')
            ->with($messageName)
            ->willReturn($message);

        $factory = new MandrillHandlerFactory();

        $handler = $factory($container, '', ['apiKey' => $apiKey, 'message' => $messageName]);

        self::assertInstanceOf(MandrillHandler::class, $handler);

        self::assertSame(Logger::DEBUG, $handler->getLevel());
        self::assertTrue($handler->getBubble());

        $messageP = new ReflectionProperty($handler, 'message');
        $messageP->setAccessible(true);

        self::assertSame($message, $messageP->getValue($handler));

        $ak = new ReflectionProperty($handler, 'apiKey');
        $ak->setAccessible(true);

        self::assertSame($apiKey, $ak->getValue($handler));

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
    public function testInvoceWithConfig5(): void
    {
        $apiKey      = 'test-key';
        $messageName = 'test-message';
        $message     = $this->getMockBuilder(Swift_Message::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::once())
            ->method('has')
            ->with($messageName)
            ->willReturn(true);
        $container->expects(self::once())
            ->method('get')
            ->with($messageName)
            ->willReturn(true);

        $factory = new MandrillHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not create %s', MandrillHandler::class));

        $factory($container, '', ['apiKey' => $apiKey, 'message' => $messageName]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvoceWithConfig6(): void
    {
        $apiKey      = 'test-key';
        $messageName = 'test-message';
        $message     = $this->getMockBuilder(Swift_Message::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::once())
            ->method('has')
            ->with($messageName)
            ->willReturn(true);
        $container->expects(self::once())
            ->method('get')
            ->with($messageName)
            ->willReturn($message);

        $factory = new MandrillHandlerFactory();

        $handler = $factory($container, '', ['apiKey' => $apiKey, 'message' => $messageName, 'level' => LogLevel::ALERT, 'bubble' => false]);

        self::assertInstanceOf(MandrillHandler::class, $handler);

        self::assertSame(Logger::ALERT, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $messageP = new ReflectionProperty($handler, 'message');
        $messageP->setAccessible(true);

        self::assertSame($message, $messageP->getValue($handler));

        $ak = new ReflectionProperty($handler, 'apiKey');
        $ak->setAccessible(true);

        self::assertSame($apiKey, $ak->getValue($handler));

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
    public function testInvoceWithConfig7(): void
    {
        $apiKey  = 'test-key';
        $message = $this->getMockBuilder(Swift_Message::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new MandrillHandlerFactory();

        $handler = $factory($container, '', ['apiKey' => $apiKey, 'message' => $message]);

        self::assertInstanceOf(MandrillHandler::class, $handler);

        self::assertSame(Logger::DEBUG, $handler->getLevel());
        self::assertTrue($handler->getBubble());

        $messageP = new ReflectionProperty($handler, 'message');
        $messageP->setAccessible(true);

        self::assertSame($message, $messageP->getValue($handler));

        $ak = new ReflectionProperty($handler, 'apiKey');
        $ak->setAccessible(true);

        self::assertSame($apiKey, $ak->getValue($handler));

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
    public function testInvoceWithConfig8(): void
    {
        $apiKey  = 'test-key';
        $message = $this->getMockBuilder(Swift_Message::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new MandrillHandlerFactory();

        $handler = $factory($container, '', ['apiKey' => $apiKey, 'message' => $message, 'level' => LogLevel::ALERT, 'bubble' => false]);

        self::assertInstanceOf(MandrillHandler::class, $handler);

        self::assertSame(Logger::ALERT, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $messageP = new ReflectionProperty($handler, 'message');
        $messageP->setAccessible(true);

        self::assertSame($message, $messageP->getValue($handler));

        $ak = new ReflectionProperty($handler, 'apiKey');
        $ak->setAccessible(true);

        self::assertSame($apiKey, $ak->getValue($handler));

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
    public function testInvoceWithConfig9(): void
    {
        $apiKey       = 'test-key';
        $messageClass = $this->getMockBuilder(Swift_Message::class)
            ->disableOriginalConstructor()
            ->getMock();
        $message      = static fn (): Swift_Message => $messageClass;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new MandrillHandlerFactory();

        $handler = $factory($container, '', ['apiKey' => $apiKey, 'message' => $message]);

        self::assertInstanceOf(MandrillHandler::class, $handler);

        self::assertSame(Logger::DEBUG, $handler->getLevel());
        self::assertTrue($handler->getBubble());

        $messageP = new ReflectionProperty($handler, 'message');
        $messageP->setAccessible(true);

        self::assertSame($messageClass, $messageP->getValue($handler));

        $ak = new ReflectionProperty($handler, 'apiKey');
        $ak->setAccessible(true);

        self::assertSame($apiKey, $ak->getValue($handler));

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
    public function testInvoceWithConfig10(): void
    {
        $apiKey       = 'test-key';
        $messageClass = $this->getMockBuilder(Swift_Message::class)
            ->disableOriginalConstructor()
            ->getMock();
        $message      = static fn (): Swift_Message => $messageClass;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new MandrillHandlerFactory();

        $handler = $factory($container, '', ['apiKey' => $apiKey, 'message' => $message, 'level' => LogLevel::ALERT, 'bubble' => false]);

        self::assertInstanceOf(MandrillHandler::class, $handler);

        self::assertSame(Logger::ALERT, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $messageP = new ReflectionProperty($handler, 'message');
        $messageP->setAccessible(true);

        self::assertSame($messageClass, $messageP->getValue($handler));

        $ak = new ReflectionProperty($handler, 'apiKey');
        $ak->setAccessible(true);

        self::assertSame($apiKey, $ak->getValue($handler));

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
    public function testInvoceWithConfigAndBoolFormatter(): void
    {
        $apiKey       = 'test-key';
        $messageClass = $this->getMockBuilder(Swift_Message::class)
            ->disableOriginalConstructor()
            ->getMock();
        $message      = static fn (): Swift_Message => $messageClass;
        $formatter    = true;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new MandrillHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class)
        );

        $factory($container, '', ['apiKey' => $apiKey, 'message' => $message, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     */
    public function testInvoceWithConfigAndFormatter(): void
    {
        $apiKey       = 'test-key';
        $messageClass = $this->getMockBuilder(Swift_Message::class)
            ->disableOriginalConstructor()
            ->getMock();
        $message      = static fn (): Swift_Message => $messageClass;
        $formatter    = $this->getMockBuilder(LineFormatter::class)
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

        $factory = new MandrillHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologFormatterPluginManager::class)
        );

        $factory($container, '', ['apiKey' => $apiKey, 'message' => $message, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvoceWithConfigAndFormatter2(): void
    {
        $apiKey       = 'test-key';
        $messageClass = $this->getMockBuilder(Swift_Message::class)
            ->disableOriginalConstructor()
            ->getMock();
        $message      = static fn (): Swift_Message => $messageClass;
        $formatter    = $this->getMockBuilder(LineFormatter::class)
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

        $factory = new MandrillHandlerFactory();

        $handler = $factory($container, '', ['apiKey' => $apiKey, 'message' => $message, 'level' => LogLevel::ALERT, 'bubble' => false, 'formatter' => $formatter]);

        self::assertInstanceOf(MandrillHandler::class, $handler);

        self::assertSame(Logger::ALERT, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $messageP = new ReflectionProperty($handler, 'message');
        $messageP->setAccessible(true);

        self::assertSame($messageClass, $messageP->getValue($handler));

        $ak = new ReflectionProperty($handler, 'apiKey');
        $ak->setAccessible(true);

        self::assertSame($apiKey, $ak->getValue($handler));

        self::assertSame($formatter, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');
        $proc->setAccessible(true);

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }
}
