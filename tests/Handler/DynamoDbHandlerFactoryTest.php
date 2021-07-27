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

use Aws\DynamoDb\DynamoDbClient;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Mimmi20\LoggerFactory\Handler\DynamoDbHandlerFactory;
use Monolog\Handler\DynamoDbHandler;
use Monolog\Logger;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

final class DynamoDbHandlerFactoryTest extends TestCase
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

        $factory = new DynamoDbHandlerFactory();

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

        $factory = new DynamoDbHandlerFactory();

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
        $clientName = true;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new DynamoDbHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No Service name provided for the required service class');

        $factory($container, '', ['client' => $clientName]);
    }

    /**
     * @throws Exception
     */
    public function testInvoceWithConfig2(): void
    {
        $clientName = 'test-client';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($clientName)
            ->willThrowException(new ServiceNotFoundException());

        $factory = new DynamoDbHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Could not load client class');

        $factory($container, '', ['client' => $clientName]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvoceWithConfig3(): void
    {
        $clientName = 'test-client';
        $client     = $this->getMockBuilder(DynamoDbClient::class)
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

        $factory = new DynamoDbHandlerFactory();

        $handler = $factory($container, '', ['client' => $clientName]);

        self::assertInstanceOf(DynamoDbHandler::class, $handler);

        self::assertSame(Logger::DEBUG, $handler->getLevel());
        self::assertTrue($handler->getBubble());

        $clientP = new ReflectionProperty($handler, 'client');
        $clientP->setAccessible(true);

        self::assertSame($client, $clientP->getValue($handler));

        $tableP = new ReflectionProperty($handler, 'table');
        $tableP->setAccessible(true);

        self::assertSame('', $tableP->getValue($handler));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvoceWithConfig4(): void
    {
        $clientName = 'test-client';
        $client     = $this->getMockBuilder(DynamoDbClient::class)
            ->disableOriginalConstructor()
            ->getMock();
        $table      = 'test-table';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with($clientName)
            ->willReturn($client);

        $factory = new DynamoDbHandlerFactory();

        $handler = $factory($container, '', ['client' => $clientName, 'table' => $table, 'level' => LogLevel::ALERT, 'bubble' => false]);

        self::assertInstanceOf(DynamoDbHandler::class, $handler);

        self::assertSame(Logger::ALERT, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $clientP = new ReflectionProperty($handler, 'client');
        $clientP->setAccessible(true);

        self::assertSame($client, $clientP->getValue($handler));

        $tableP = new ReflectionProperty($handler, 'table');
        $tableP->setAccessible(true);

        self::assertSame($table, $tableP->getValue($handler));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvoceWithConfig5(): void
    {
        $client = $this->getMockBuilder(DynamoDbClient::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new DynamoDbHandlerFactory();

        $handler = $factory($container, '', ['client' => $client]);

        self::assertInstanceOf(DynamoDbHandler::class, $handler);

        self::assertSame(Logger::DEBUG, $handler->getLevel());
        self::assertTrue($handler->getBubble());

        $clientP = new ReflectionProperty($handler, 'client');
        $clientP->setAccessible(true);

        self::assertSame($client, $clientP->getValue($handler));

        $tableP = new ReflectionProperty($handler, 'table');
        $tableP->setAccessible(true);

        self::assertSame('', $tableP->getValue($handler));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvoceWithConfig6(): void
    {
        $client = $this->getMockBuilder(DynamoDbClient::class)
            ->disableOriginalConstructor()
            ->getMock();
        $table  = 'test-table';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new DynamoDbHandlerFactory();

        $handler = $factory($container, '', ['client' => $client, 'table' => $table, 'level' => LogLevel::ALERT, 'bubble' => false]);

        self::assertInstanceOf(DynamoDbHandler::class, $handler);

        self::assertSame(Logger::ALERT, $handler->getLevel());
        self::assertFalse($handler->getBubble());

        $clientP = new ReflectionProperty($handler, 'client');
        $clientP->setAccessible(true);

        self::assertSame($client, $clientP->getValue($handler));

        $tableP = new ReflectionProperty($handler, 'table');
        $tableP->setAccessible(true);

        self::assertSame($table, $tableP->getValue($handler));
    }
}
