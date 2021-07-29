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
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Mimmi20\LoggerFactory\Handler\MongoDBHandlerFactory;
use MongoDB\Client;
use MongoDB\Driver\Exception\RuntimeException;
use MongoDB\Driver\Manager;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\MongoDBHandler;
use Monolog\Logger;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

use function class_exists;
use function sprintf;

final class MongoDBHandlerFactoryTest extends TestCase
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

        $factory = new MongoDBHandlerFactory();

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

        $factory = new MongoDBHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No Service name provided for the required service class');

        $factory($container, '', []);
    }

    /**
     * @throws Exception
     */
    public function testInvoceWithConfig(): void
    {
        $client = true;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new MongoDBHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No database provided');

        $factory($container, '', ['client' => $client]);
    }

    /**
     * @throws Exception
     */
    public function testInvoceWithConfig2(): void
    {
        $client   = true;
        $database = 'test-database';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new MongoDBHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No collection provided');

        $factory($container, '', ['client' => $client, 'database' => $database]);
    }

    /**
     * @throws Exception
     */
    public function testInvoceWithConfig3(): void
    {
        $client     = true;
        $database   = 'test-database';
        $collection = 'test-collection';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new MongoDBHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No Service name provided for the required service class');

        $factory($container, '', ['client' => $client, 'database' => $database, 'collection' => $collection]);
    }

    /**
     * @throws Exception
     */
    public function testInvoceWithConfig4(): void
    {
        $client     = 'test-client';
        $database   = 'test-database';
        $collection = 'test-collection';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($client)
            ->willThrowException(new ServiceNotFoundException());

        $factory = new MongoDBHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Could not load client class');

        $factory($container, '', ['client' => $client, 'database' => $database, 'collection' => $collection]);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function testInvoceWithConfig5(): void
    {
        if (!class_exists(Client::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Client::class));
        }

        $client      = 'test-client';
        $clientClass = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $database    = 'test-database';
        $collection  = 'test-collection';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($client)
            ->willReturn($clientClass);

        $factory = new MongoDBHandlerFactory();

        $handler = $factory($container, '', ['client' => $client, 'database' => $database, 'collection' => $collection]);

        self::assertInstanceOf(MongoDBHandler::class, $handler);

        self::assertSame(Logger::DEBUG, $handler->getLevel());
        self::assertTrue($handler->getBubble());

        self::assertInstanceOf(LineFormatter::class, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');
        $proc->setAccessible(true);

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function testInvoceWithConfig6(): void
    {
        if (!class_exists(Client::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Client::class));
        }

        $client     = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $database   = 'test-database';
        $collection = 'test-collection';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new MongoDBHandlerFactory();

        $handler = $factory($container, '', ['client' => $client, 'database' => $database, 'collection' => $collection]);

        self::assertInstanceOf(MongoDBHandler::class, $handler);

        self::assertSame(Logger::DEBUG, $handler->getLevel());
        self::assertTrue($handler->getBubble());

        self::assertInstanceOf(LineFormatter::class, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');
        $proc->setAccessible(true);

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws \MongoDB\Driver\Exception\InvalidArgumentException
     * @throws ReflectionException
     */
    public function testInvoceWithConfig7(): void
    {
        if (!class_exists(Manager::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Manager::class));
        }

        $client      = 'test-client';
        $clientClass = new Manager('mongodb://example.com:27017');
        $database    = 'test-database';
        $collection  = 'test-collection';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($client)
            ->willReturn($clientClass);

        $factory = new MongoDBHandlerFactory();

        $handler = $factory($container, '', ['client' => $client, 'database' => $database, 'collection' => $collection]);

        self::assertInstanceOf(MongoDBHandler::class, $handler);

        self::assertSame(Logger::DEBUG, $handler->getLevel());
        self::assertTrue($handler->getBubble());

        self::assertInstanceOf(LineFormatter::class, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');
        $proc->setAccessible(true);

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws \MongoDB\Driver\Exception\InvalidArgumentException
     * @throws ReflectionException
     */
    public function testInvoceWithConfig8(): void
    {
        if (!class_exists(Manager::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Manager::class));
        }

        $client     = new Manager('mongodb://example.com:27017');
        $database   = 'test-database';
        $collection = 'test-collection';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new MongoDBHandlerFactory();

        $handler = $factory($container, '', ['client' => $client, 'database' => $database, 'collection' => $collection]);

        self::assertInstanceOf(MongoDBHandler::class, $handler);

        self::assertSame(Logger::DEBUG, $handler->getLevel());
        self::assertTrue($handler->getBubble());

        self::assertInstanceOf(LineFormatter::class, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');
        $proc->setAccessible(true);

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function testInvoceWithConfig9(): void
    {
        if (!class_exists(Client::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Client::class));
        }

        $level  = LogLevel::ALERT;
        $bubble = false;

        $client      = 'test-client';
        $clientClass = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $database    = 'test-database';
        $collection  = 'test-collection';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($client)
            ->willReturn($clientClass);

        $factory = new MongoDBHandlerFactory();

        $handler = $factory($container, '', ['client' => $client, 'database' => $database, 'collection' => $collection, 'level' => $level, 'bubble' => $bubble]);

        self::assertInstanceOf(MongoDBHandler::class, $handler);

        self::assertSame(Logger::ALERT, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        self::assertInstanceOf(LineFormatter::class, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');
        $proc->setAccessible(true);

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function testInvoceWithConfig10(): void
    {
        if (!class_exists(Client::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Client::class));
        }

        $level  = LogLevel::ALERT;
        $bubble = false;

        $client     = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $database   = 'test-database';
        $collection = 'test-collection';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new MongoDBHandlerFactory();

        $handler = $factory($container, '', ['client' => $client, 'database' => $database, 'collection' => $collection, 'level' => $level, 'bubble' => $bubble]);

        self::assertInstanceOf(MongoDBHandler::class, $handler);

        self::assertSame(Logger::ALERT, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        self::assertInstanceOf(LineFormatter::class, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');
        $proc->setAccessible(true);

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws \MongoDB\Driver\Exception\InvalidArgumentException
     * @throws ReflectionException
     */
    public function testInvoceWithConfig11(): void
    {
        if (!class_exists(Manager::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Manager::class));
        }

        $level  = LogLevel::ALERT;
        $bubble = false;

        $client      = 'test-client';
        $clientClass = new Manager('mongodb://example.com:27017');
        $database    = 'test-database';
        $collection  = 'test-collection';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($client)
            ->willReturn($clientClass);

        $factory = new MongoDBHandlerFactory();

        $handler = $factory($container, '', ['client' => $client, 'database' => $database, 'collection' => $collection, 'level' => $level, 'bubble' => $bubble]);

        self::assertInstanceOf(MongoDBHandler::class, $handler);

        self::assertSame(Logger::ALERT, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        self::assertInstanceOf(LineFormatter::class, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');
        $proc->setAccessible(true);

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws \MongoDB\Driver\Exception\InvalidArgumentException
     * @throws ReflectionException
     */
    public function testInvoceWithConfig12(): void
    {
        if (!class_exists(Manager::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Manager::class));
        }

        $level  = LogLevel::ALERT;
        $bubble = false;

        $client     = new Manager('mongodb://example.com:27017');
        $database   = 'test-database';
        $collection = 'test-collection';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new MongoDBHandlerFactory();

        $handler = $factory($container, '', ['client' => $client, 'database' => $database, 'collection' => $collection, 'level' => $level, 'bubble' => $bubble]);

        self::assertInstanceOf(MongoDBHandler::class, $handler);

        self::assertSame(Logger::ALERT, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        self::assertInstanceOf(LineFormatter::class, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');
        $proc->setAccessible(true);

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws \MongoDB\Driver\Exception\InvalidArgumentException
     */
    public function testInvoceWithConfigAndBoolFormatter(): void
    {
        if (!class_exists(Manager::class)) {
            self::markTestSkipped(sprintf('class %s is required for this test', Manager::class));
        }

        $level     = LogLevel::ALERT;
        $bubble    = false;
        $formatter = true;

        $client     = new Manager('mongodb://example.com:27017');
        $database   = 'test-database';
        $collection = 'test-collection';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new MongoDBHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class)
        );

        $factory($container, '', ['client' => $client, 'database' => $database, 'collection' => $collection, 'level' => $level, 'bubble' => $bubble, 'formatter' => $formatter]);
    }
}
