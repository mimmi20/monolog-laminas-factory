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
use Mimmi20\LoggerFactory\Handler\RedisHandlerFactory;
use Mimmi20\LoggerFactory\MonologFormatterPluginManager;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RedisHandler;
use Monolog\Logger;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Predis\Client;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

use function sprintf;

final class RedisHandlerFactoryTest extends TestCase
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

        $factory = new RedisHandlerFactory();

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

        $factory = new RedisHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No Service name provided for the required service class');

        $factory($container, '', []);
    }

    /**
     * @throws Exception
     */
    public function testInvoceWithWrongClient(): void
    {
        $client = true;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new RedisHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No Service name provided for the required service class');

        $factory($container, '', ['client' => $client]);
    }

    /**
     * @throws Exception
     */
    public function testInvoceWithWrongClient2(): void
    {
        $client = 'abc';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($client)
            ->willThrowException(new ServiceNotFoundException());

        $factory = new RedisHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Could not load client class');

        $factory($container, '', ['client' => $client]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvoceWithClient(): void
    {
        $clientName = 'abc';
        $client     = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($clientName)
            ->willReturn($client);

        $factory = new RedisHandlerFactory();

        $handler = $factory($container, '', ['client' => $clientName]);

        self::assertInstanceOf(RedisHandler::class, $handler);

        self::assertSame(Logger::DEBUG, $handler->getLevel());
        self::assertTrue($handler->getBubble());

        $rc = new ReflectionProperty($handler, 'redisClient');
        $rc->setAccessible(true);

        self::assertSame($client, $rc->getValue($handler));

        $ck = new ReflectionProperty($handler, 'redisKey');
        $ck->setAccessible(true);

        self::assertSame('', $ck->getValue($handler));

        $cs = new ReflectionProperty($handler, 'capSize');
        $cs->setAccessible(true);

        self::assertSame(0, $cs->getValue($handler));

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
    public function testInvoceWithClient2(): void
    {
        $clientName = 'abc';
        $client     = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $key        = 'test-key';
        $level      = LogLevel::ALERT;
        $bubble     = false;
        $capSize    = 42;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($clientName)
            ->willReturn($client);

        $factory = new RedisHandlerFactory();

        $handler = $factory($container, '', ['client' => $clientName, 'key' => $key, 'level' => $level, 'bubble' => $bubble, 'capSize' => $capSize]);

        self::assertInstanceOf(RedisHandler::class, $handler);

        self::assertSame(Logger::ALERT, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $rc = new ReflectionProperty($handler, 'redisClient');
        $rc->setAccessible(true);

        self::assertSame($client, $rc->getValue($handler));

        $ck = new ReflectionProperty($handler, 'redisKey');
        $ck->setAccessible(true);

        self::assertSame($key, $ck->getValue($handler));

        $cs = new ReflectionProperty($handler, 'capSize');
        $cs->setAccessible(true);

        self::assertSame($capSize, $cs->getValue($handler));

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
    public function testInvoceWithClient3(): void
    {
        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new RedisHandlerFactory();

        $handler = $factory($container, '', ['client' => $client]);

        self::assertInstanceOf(RedisHandler::class, $handler);

        self::assertSame(Logger::DEBUG, $handler->getLevel());
        self::assertTrue($handler->getBubble());

        $rc = new ReflectionProperty($handler, 'redisClient');
        $rc->setAccessible(true);

        self::assertSame($client, $rc->getValue($handler));

        $ck = new ReflectionProperty($handler, 'redisKey');
        $ck->setAccessible(true);

        self::assertSame('', $ck->getValue($handler));

        $cs = new ReflectionProperty($handler, 'capSize');
        $cs->setAccessible(true);

        self::assertSame(0, $cs->getValue($handler));

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
    public function testInvoceWithClient4(): void
    {
        $client  = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $key     = 'test-key';
        $level   = LogLevel::ALERT;
        $bubble  = false;
        $capSize = 42;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new RedisHandlerFactory();

        $handler = $factory($container, '', ['client' => $client, 'key' => $key, 'level' => $level, 'bubble' => $bubble, 'capSize' => $capSize]);

        self::assertInstanceOf(RedisHandler::class, $handler);

        self::assertSame(Logger::ALERT, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $rc = new ReflectionProperty($handler, 'redisClient');
        $rc->setAccessible(true);

        self::assertSame($client, $rc->getValue($handler));

        $ck = new ReflectionProperty($handler, 'redisKey');
        $ck->setAccessible(true);

        self::assertSame($key, $ck->getValue($handler));

        $cs = new ReflectionProperty($handler, 'capSize');
        $cs->setAccessible(true);

        self::assertSame($capSize, $cs->getValue($handler));

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
    public function testInvoceWithClient5(): void
    {
        $clientName = 'abc';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($clientName)
            ->willReturn(true);

        $factory = new RedisHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not load class %s', RedisHandler::class));

        $factory($container, '', ['client' => $clientName]);
    }

    /**
     * @throws Exception
     */
    public function testInvoceWithConfigAndBoolFormatter(): void
    {
        $client    = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $key       = 'test-key';
        $level     = LogLevel::ALERT;
        $bubble    = false;
        $capSize   = 42;
        $formatter = true;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new RedisHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class)
        );

        $factory($container, '', ['client' => $client, 'key' => $key, 'level' => $level, 'bubble' => $bubble, 'capSize' => $capSize, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     */
    public function testInvoceWithConfigAndFormatter(): void
    {
        $client    = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $key       = 'test-key';
        $level     = LogLevel::ALERT;
        $bubble    = false;
        $capSize   = 42;
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

        $factory = new RedisHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologFormatterPluginManager::class)
        );

        $factory($container, '', ['client' => $client, 'key' => $key, 'level' => $level, 'bubble' => $bubble, 'capSize' => $capSize, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvoceWithConfigAndFormatter2(): void
    {
        $client    = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $key       = 'test-key';
        $level     = LogLevel::ALERT;
        $bubble    = false;
        $capSize   = 42;
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

        $factory = new RedisHandlerFactory();

        $handler = $factory($container, '', ['client' => $client, 'key' => $key, 'level' => $level, 'bubble' => $bubble, 'capSize' => $capSize, 'formatter' => $formatter]);

        self::assertInstanceOf(RedisHandler::class, $handler);

        self::assertSame(Logger::ALERT, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $rc = new ReflectionProperty($handler, 'redisClient');
        $rc->setAccessible(true);

        self::assertSame($client, $rc->getValue($handler));

        $ck = new ReflectionProperty($handler, 'redisKey');
        $ck->setAccessible(true);

        self::assertSame($key, $ck->getValue($handler));

        $cs = new ReflectionProperty($handler, 'capSize');
        $cs->setAccessible(true);

        self::assertSame($capSize, $cs->getValue($handler));

        self::assertSame($formatter, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');
        $proc->setAccessible(true);

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }
}
