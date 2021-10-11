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
use Mimmi20\LoggerFactory\Handler\SendGridHandlerFactory;
use Mimmi20\LoggerFactory\MonologFormatterPluginManager;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\HtmlFormatter;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\SendGridHandler;
use Monolog\Logger;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

use function extension_loaded;
use function sprintf;

final class SendGridHandlerFactoryTest extends TestCase
{
    /**
     * @throws Exception
     *
     * @requires extension curl
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

        $factory = new SendGridHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Options must be an Array');

        $factory($container, '');
    }

    /**
     * @throws Exception
     *
     * @requires extension curl
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

        $factory = new SendGridHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('The required apiUser is missing');

        $factory($container, '', []);
    }

    /**
     * @throws Exception
     *
     * @requires extension curl
     */
    public function testInvoceWithConfig(): void
    {
        $apiUser = 'test-api-user';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new SendGridHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('The required apiKey is missing');

        $factory($container, '', ['apiUser' => $apiUser]);
    }

    /**
     * @throws Exception
     *
     * @requires extension curl
     */
    public function testInvoceWithConfig2(): void
    {
        $apiUser = 'test-api-user';
        $apiKey  = 'test-api-key';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new SendGridHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('The required from is missing');

        $factory($container, '', ['apiUser' => $apiUser, 'apiKey' => $apiKey]);
    }

    /**
     * @throws Exception
     *
     * @requires extension curl
     */
    public function testInvoceWithConfig3(): void
    {
        $apiUser = 'test-api-user';
        $apiKey  = 'test-api-key';
        $from    = 'test-from';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new SendGridHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('The required to is missing');

        $factory($container, '', ['apiUser' => $apiUser, 'apiKey' => $apiKey, 'from' => $from]);
    }

    /**
     * @throws Exception
     *
     * @requires extension curl
     */
    public function testInvoceWithConfig4(): void
    {
        $apiUser = 'test-api-user';
        $apiKey  = 'test-api-key';
        $from    = 'test-from';
        $to      = 'test-to';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new SendGridHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('The required subject is missing');

        $factory($container, '', ['apiUser' => $apiUser, 'apiKey' => $apiKey, 'from' => $from, 'to' => $to]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     *
     * @requires extension curl
     */
    public function testInvoceWithConfig5(): void
    {
        $apiUser = 'test-api-user';
        $apiKey  = 'test-api-key';
        $from    = 'test-from';
        $to      = 'test-to';
        $subject = 'test-subject';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new SendGridHandlerFactory();

        $handler = $factory($container, '', ['apiUser' => $apiUser, 'apiKey' => $apiKey, 'from' => $from, 'to' => $to, 'subject' => $subject]);

        self::assertInstanceOf(SendGridHandler::class, $handler);

        self::assertSame(Logger::DEBUG, $handler->getLevel());
        self::assertTrue($handler->getBubble());

        $apiUserP = new ReflectionProperty($handler, 'apiUser');
        $apiUserP->setAccessible(true);

        self::assertSame($apiUser, $apiUserP->getValue($handler));

        $apiKeyP = new ReflectionProperty($handler, 'apiKey');
        $apiKeyP->setAccessible(true);

        self::assertSame($apiKey, $apiKeyP->getValue($handler));

        $fromP = new ReflectionProperty($handler, 'from');
        $fromP->setAccessible(true);

        self::assertSame($from, $fromP->getValue($handler));

        $toP = new ReflectionProperty($handler, 'to');
        $toP->setAccessible(true);

        self::assertSame((array) $to, $toP->getValue($handler));

        $subjectP = new ReflectionProperty($handler, 'subject');
        $subjectP->setAccessible(true);

        self::assertSame($subject, $subjectP->getValue($handler));

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
     *
     * @requires extension curl
     */
    public function testInvoceWithConfig6(): void
    {
        $apiUser = 'test-api-user';
        $apiKey  = 'test-api-key';
        $from    = 'test-from';
        $to      = 'test-to';
        $subject = 'test-subject';
        $level   = LogLevel::ALERT;
        $bubble  = false;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new SendGridHandlerFactory();

        $handler = $factory($container, '', ['apiUser' => $apiUser, 'apiKey' => $apiKey, 'from' => $from, 'to' => $to, 'subject' => $subject, 'level' => $level, 'bubble' => $bubble]);

        self::assertInstanceOf(SendGridHandler::class, $handler);

        self::assertSame(Logger::ALERT, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $apiUserP = new ReflectionProperty($handler, 'apiUser');
        $apiUserP->setAccessible(true);

        self::assertSame($apiUser, $apiUserP->getValue($handler));

        $apiKeyP = new ReflectionProperty($handler, 'apiKey');
        $apiKeyP->setAccessible(true);

        self::assertSame($apiKey, $apiKeyP->getValue($handler));

        $fromP = new ReflectionProperty($handler, 'from');
        $fromP->setAccessible(true);

        self::assertSame($from, $fromP->getValue($handler));

        $toP = new ReflectionProperty($handler, 'to');
        $toP->setAccessible(true);

        self::assertSame((array) $to, $toP->getValue($handler));

        $subjectP = new ReflectionProperty($handler, 'subject');
        $subjectP->setAccessible(true);

        self::assertSame($subject, $subjectP->getValue($handler));

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
    public function testInvoceWithoutExtension(): void
    {
        if (extension_loaded('curl')) {
            self::markTestSkipped('This test checks the exception if the curl extension is missing');
        }

        $apiUser = 'test-api-user';
        $apiKey  = 'test-api-key';
        $from    = 'test-from';
        $to      = 'test-to';
        $subject = 'test-subject';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new SendGridHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not create %s', SendGridHandler::class)
        );

        $factory($container, '', ['apiUser' => $apiUser, 'apiKey' => $apiKey, 'from' => $from, 'to' => $to, 'subject' => $subject]);
    }

    /**
     * @throws Exception
     *
     * @requires extension curl
     */
    public function testInvoceWithConfigAndBoolFormatter(): void
    {
        $apiUser   = 'test-api-user';
        $apiKey    = 'test-api-key';
        $from      = 'test-from';
        $to        = 'test-to';
        $subject   = 'test-subject';
        $level     = LogLevel::ALERT;
        $bubble    = false;
        $formatter = true;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new SendGridHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class)
        );

        $factory($container, '', ['apiUser' => $apiUser, 'apiKey' => $apiKey, 'from' => $from, 'to' => $to, 'subject' => $subject, 'level' => $level, 'bubble' => $bubble, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     *
     * @requires extension curl
     */
    public function testInvoceWithConfigAndFormatter(): void
    {
        $apiUser   = 'test-api-user';
        $apiKey    = 'test-api-key';
        $from      = 'test-from';
        $to        = 'test-to';
        $subject   = 'test-subject';
        $level     = LogLevel::ALERT;
        $bubble    = false;
        $formatter = $this->getMockBuilder(LineFormatter::class)
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

        $factory = new SendGridHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologFormatterPluginManager::class)
        );

        $factory($container, '', ['apiUser' => $apiUser, 'apiKey' => $apiKey, 'from' => $from, 'to' => $to, 'subject' => $subject, 'level' => $level, 'bubble' => $bubble, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     *
     * @requires extension curl
     */
    public function testInvoceWithConfigAndFormatter2(): void
    {
        $apiUser   = 'test-api-user';
        $apiKey    = 'test-api-key';
        $from      = 'test-from';
        $to        = 'test-to';
        $subject   = 'test-subject';
        $level     = LogLevel::ALERT;
        $bubble    = false;
        $formatter = $this->getMockBuilder(LineFormatter::class)
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

        $factory = new SendGridHandlerFactory();

        $handler = $factory($container, '', ['apiUser' => $apiUser, 'apiKey' => $apiKey, 'from' => $from, 'to' => $to, 'subject' => $subject, 'level' => $level, 'bubble' => $bubble, 'formatter' => $formatter]);

        self::assertInstanceOf(SendGridHandler::class, $handler);

        self::assertSame(Logger::ALERT, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $apiUserP = new ReflectionProperty($handler, 'apiUser');
        $apiUserP->setAccessible(true);

        self::assertSame($apiUser, $apiUserP->getValue($handler));

        $apiKeyP = new ReflectionProperty($handler, 'apiKey');
        $apiKeyP->setAccessible(true);

        self::assertSame($apiKey, $apiKeyP->getValue($handler));

        $fromP = new ReflectionProperty($handler, 'from');
        $fromP->setAccessible(true);

        self::assertSame($from, $fromP->getValue($handler));

        $toP = new ReflectionProperty($handler, 'to');
        $toP->setAccessible(true);

        self::assertSame((array) $to, $toP->getValue($handler));

        $subjectP = new ReflectionProperty($handler, 'subject');
        $subjectP->setAccessible(true);

        self::assertSame($subject, $subjectP->getValue($handler));

        self::assertSame($formatter, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');
        $proc->setAccessible(true);

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     *
     * @requires extension curl
     */
    public function testInvoceWithConfigAndBoolProcessors(): void
    {
        $apiUser    = 'test-api-user';
        $apiKey     = 'test-api-key';
        $from       = 'test-from';
        $to         = 'test-to';
        $subject    = 'test-subject';
        $level      = LogLevel::ALERT;
        $bubble     = false;
        $processors = true;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new SendGridHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Processors must be an Array');

        $factory($container, '', ['apiUser' => $apiUser, 'apiKey' => $apiKey, 'from' => $from, 'to' => $to, 'subject' => $subject, 'level' => $level, 'bubble' => $bubble, 'processors' => $processors]);
    }
}
