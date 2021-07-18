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
use Mimmi20\LoggerFactory\Handler\FingersCrossed\ActivationStrategyPluginManager;
use Mimmi20\LoggerFactory\Handler\FingersCrossedHandlerFactory;
use Mimmi20\LoggerFactory\MonologHandlerPluginManager;
use Monolog\Handler\ChromePHPHandler;
use Monolog\Handler\FingersCrossed\ChannelLevelActivationStrategy;
use Monolog\Handler\FingersCrossed\ErrorLevelActivationStrategy;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Logger;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

use function sprintf;

final class FingersCrossedHandlerFactoryTest extends TestCase
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

        $factory = new FingersCrossedHandlerFactory();

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

        $factory = new FingersCrossedHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No handler provided');

        $factory($container, '', []);
    }

    /**
     * @throws Exception
     */
    public function testInvoceWithoutHandlerConfig(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new FingersCrossedHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('HandlerConfig must be an Array');

        $factory($container, '', ['handler' => true]);
    }

    /**
     * @throws Exception
     */
    public function testInvoceWithHandlerConfigWithoutType(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new FingersCrossedHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Options must contain a type for the handler');

        $factory($container, '', ['handler' => []]);
    }

    /**
     * @throws Exception
     */
    public function testInvoceWithHandlerConfigWithDisabledType(): void
    {
        $type = 'abc';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new FingersCrossedHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No active handler specified');

        $factory($container, '', ['handler' => ['type' => $type, 'enabled' => false]]);
    }

    /**
     * @throws Exception
     */
    public function testInvoceWithHandlerConfigWithLoaderError(): void
    {
        $type = 'abc';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologHandlerPluginManager::class)
            ->willThrowException(new ServiceNotCreatedException());

        $factory = new FingersCrossedHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not load handler class %s', $type));

        $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true]]);
    }

    /**
     * @throws Exception
     */
    public function testInvoceWithHandlerConfigWithLoaderError2(): void
    {
        $type = 'abc';

        $monologHandlerPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::once())
            ->method('get')
            ->with($type)
            ->willThrowException(new ServiceNotFoundException());

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologHandlerPluginManager::class)
            ->willReturn($monologHandlerPluginManager);

        $factory = new FingersCrossedHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not load handler class %s', $type));

        $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true]]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvoceWithHandlerConfig(): void
    {
        $type = 'abc';

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();

        $monologHandlerPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::once())
            ->method('get')
            ->with($type)
            ->willReturn($handler2);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologHandlerPluginManager::class)
            ->willReturn($monologHandlerPluginManager);

        $factory = new FingersCrossedHandlerFactory();

        $handler = $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true]]);

        self::assertInstanceOf(FingersCrossedHandler::class, $handler);

        $handlerP = new ReflectionProperty($handler, 'handler');
        $handlerP->setAccessible(true);

        self::assertSame($handler2, $handlerP->getValue($handler));

        $as = new ReflectionProperty($handler, 'activationStrategy');
        $as->setAccessible(true);

        self::assertInstanceOf(ErrorLevelActivationStrategy::class, $as->getValue($handler));

        $bs = new ReflectionProperty($handler, 'bufferSize');
        $bs->setAccessible(true);

        self::assertSame(0, $bs->getValue($handler));

        $b = new ReflectionProperty($handler, 'bubble');
        $b->setAccessible(true);

        self::assertTrue($b->getValue($handler));

        $sb = new ReflectionProperty($handler, 'stopBuffering');
        $sb->setAccessible(true);

        self::assertTrue($sb->getValue($handler));

        $ptl = new ReflectionProperty($handler, 'passthruLevel');
        $ptl->setAccessible(true);

        self::assertNull($ptl->getValue($handler));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvoceWithHandlerConfig2(): void
    {
        $type = 'abc';

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();

        $monologHandlerPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::once())
            ->method('get')
            ->with($type)
            ->willReturn($handler2);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologHandlerPluginManager::class)
            ->willReturn($monologHandlerPluginManager);

        $factory = new FingersCrossedHandlerFactory();

        $handler = $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true], 'activationStrategy' => null, 'bufferSize' => 42, 'bubble' => false, 'stopBuffering' => false, 'passthruLevel' => LogLevel::WARNING]);

        self::assertInstanceOf(FingersCrossedHandler::class, $handler);

        $handlerP = new ReflectionProperty($handler, 'handler');
        $handlerP->setAccessible(true);

        self::assertSame($handler2, $handlerP->getValue($handler));

        $as = new ReflectionProperty($handler, 'activationStrategy');
        $as->setAccessible(true);

        self::assertInstanceOf(ErrorLevelActivationStrategy::class, $as->getValue($handler));

        $bs = new ReflectionProperty($handler, 'bufferSize');
        $bs->setAccessible(true);

        self::assertSame(42, $bs->getValue($handler));

        $b = new ReflectionProperty($handler, 'bubble');
        $b->setAccessible(true);

        self::assertFalse($b->getValue($handler));

        $sb = new ReflectionProperty($handler, 'stopBuffering');
        $sb->setAccessible(true);

        self::assertFalse($sb->getValue($handler));

        $ptl = new ReflectionProperty($handler, 'passthruLevel');
        $ptl->setAccessible(true);

        self::assertSame(Logger::WARNING, $ptl->getValue($handler));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvoceWithHandlerConfig3(): void
    {
        $type = 'abc';

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();

        $monologHandlerPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::once())
            ->method('get')
            ->with($type)
            ->willReturn($handler2);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologHandlerPluginManager::class)
            ->willReturn($monologHandlerPluginManager);

        $factory = new FingersCrossedHandlerFactory();

        $handler = $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true], 'activationStrategy' => Logger::WARNING, 'bufferSize' => 42, 'bubble' => false, 'stopBuffering' => false, 'passthruLevel' => LogLevel::WARNING]);

        self::assertInstanceOf(FingersCrossedHandler::class, $handler);

        $handlerP = new ReflectionProperty($handler, 'handler');
        $handlerP->setAccessible(true);

        self::assertSame($handler2, $handlerP->getValue($handler));

        $as = new ReflectionProperty($handler, 'activationStrategy');
        $as->setAccessible(true);

        self::assertInstanceOf(ErrorLevelActivationStrategy::class, $as->getValue($handler));

        $bs = new ReflectionProperty($handler, 'bufferSize');
        $bs->setAccessible(true);

        self::assertSame(42, $bs->getValue($handler));

        $b = new ReflectionProperty($handler, 'bubble');
        $b->setAccessible(true);

        self::assertFalse($b->getValue($handler));

        $sb = new ReflectionProperty($handler, 'stopBuffering');
        $sb->setAccessible(true);

        self::assertFalse($sb->getValue($handler));

        $ptl = new ReflectionProperty($handler, 'passthruLevel');
        $ptl->setAccessible(true);

        self::assertSame(Logger::WARNING, $ptl->getValue($handler));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvoceWithHandlerConfig4(): void
    {
        $type     = 'abc';
        $strategy = $this->getMockBuilder(ChannelLevelActivationStrategy::class)
            ->disableOriginalConstructor()
            ->getMock();

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();

        $monologHandlerPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::once())
            ->method('get')
            ->with($type)
            ->willReturn($handler2);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::once())
            ->method('get')
            ->with(MonologHandlerPluginManager::class)
            ->willReturn($monologHandlerPluginManager);

        $factory = new FingersCrossedHandlerFactory();

        $handler = $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true], 'activationStrategy' => $strategy, 'bufferSize' => 42, 'bubble' => false, 'stopBuffering' => false, 'passthruLevel' => LogLevel::WARNING]);

        self::assertInstanceOf(FingersCrossedHandler::class, $handler);

        $handlerP = new ReflectionProperty($handler, 'handler');
        $handlerP->setAccessible(true);

        self::assertSame($handler2, $handlerP->getValue($handler));

        $as = new ReflectionProperty($handler, 'activationStrategy');
        $as->setAccessible(true);

        self::assertSame($strategy, $as->getValue($handler));

        $bs = new ReflectionProperty($handler, 'bufferSize');
        $bs->setAccessible(true);

        self::assertSame(42, $bs->getValue($handler));

        $b = new ReflectionProperty($handler, 'bubble');
        $b->setAccessible(true);

        self::assertFalse($b->getValue($handler));

        $sb = new ReflectionProperty($handler, 'stopBuffering');
        $sb->setAccessible(true);

        self::assertFalse($sb->getValue($handler));

        $ptl = new ReflectionProperty($handler, 'passthruLevel');
        $ptl->setAccessible(true);

        self::assertSame(Logger::WARNING, $ptl->getValue($handler));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvoceWithHandlerConfig5(): void
    {
        $type     = 'abc';
        $strategy = LogLevel::WARNING;

        $activationStrategyPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $activationStrategyPluginManager->expects(self::once())
            ->method('has')
            ->with($strategy)
            ->willReturn(false);
        $activationStrategyPluginManager->expects(self::never())
            ->method('get');

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();

        $monologHandlerPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::once())
            ->method('get')
            ->with($type)
            ->willReturn($handler2);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive([MonologHandlerPluginManager::class], [ActivationStrategyPluginManager::class])
            ->willReturnOnConsecutiveCalls($monologHandlerPluginManager, $activationStrategyPluginManager);

        $factory = new FingersCrossedHandlerFactory();

        $handler = $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true], 'activationStrategy' => $strategy, 'bufferSize' => 42, 'bubble' => false, 'stopBuffering' => false, 'passthruLevel' => LogLevel::WARNING]);

        self::assertInstanceOf(FingersCrossedHandler::class, $handler);

        $handlerP = new ReflectionProperty($handler, 'handler');
        $handlerP->setAccessible(true);

        self::assertSame($handler2, $handlerP->getValue($handler));

        $as = new ReflectionProperty($handler, 'activationStrategy');
        $as->setAccessible(true);

        self::assertInstanceOf(ErrorLevelActivationStrategy::class, $as->getValue($handler));

        $bs = new ReflectionProperty($handler, 'bufferSize');
        $bs->setAccessible(true);

        self::assertSame(42, $bs->getValue($handler));

        $b = new ReflectionProperty($handler, 'bubble');
        $b->setAccessible(true);

        self::assertFalse($b->getValue($handler));

        $sb = new ReflectionProperty($handler, 'stopBuffering');
        $sb->setAccessible(true);

        self::assertFalse($sb->getValue($handler));

        $ptl = new ReflectionProperty($handler, 'passthruLevel');
        $ptl->setAccessible(true);

        self::assertSame(Logger::WARNING, $ptl->getValue($handler));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function testInvoceWithHandlerConfig6(): void
    {
        $type          = 'abc';
        $strategy      = 'xyz';
        $strategyClass = $this->getMockBuilder(ChannelLevelActivationStrategy::class)
            ->disableOriginalConstructor()
            ->getMock();

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();

        $activationStrategyPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $activationStrategyPluginManager->expects(self::once())
            ->method('has')
            ->with($strategy)
            ->willReturn(true);
        $activationStrategyPluginManager->expects(self::once())
            ->method('get')
            ->with($strategy)
            ->willReturn($strategyClass);

        $monologHandlerPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::once())
            ->method('get')
            ->with($type)
            ->willReturn($handler2);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive([MonologHandlerPluginManager::class], [ActivationStrategyPluginManager::class])
            ->willReturnOnConsecutiveCalls($monologHandlerPluginManager, $activationStrategyPluginManager);

        $factory = new FingersCrossedHandlerFactory();

        $handler = $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true], 'activationStrategy' => $strategy, 'bufferSize' => 42, 'bubble' => false, 'stopBuffering' => false, 'passthruLevel' => LogLevel::WARNING]);

        self::assertInstanceOf(FingersCrossedHandler::class, $handler);

        $handlerP = new ReflectionProperty($handler, 'handler');
        $handlerP->setAccessible(true);

        self::assertSame($handler2, $handlerP->getValue($handler));

        $as = new ReflectionProperty($handler, 'activationStrategy');
        $as->setAccessible(true);

        self::assertSame($strategyClass, $as->getValue($handler));

        $bs = new ReflectionProperty($handler, 'bufferSize');
        $bs->setAccessible(true);

        self::assertSame(42, $bs->getValue($handler));

        $b = new ReflectionProperty($handler, 'bubble');
        $b->setAccessible(true);

        self::assertFalse($b->getValue($handler));

        $sb = new ReflectionProperty($handler, 'stopBuffering');
        $sb->setAccessible(true);

        self::assertFalse($sb->getValue($handler));

        $ptl = new ReflectionProperty($handler, 'passthruLevel');
        $ptl->setAccessible(true);

        self::assertSame(Logger::WARNING, $ptl->getValue($handler));
    }

    /**
     * @throws Exception
     * @throws ServiceNotFoundException
     */
    public function testInvoceWithHandlerConfig7(): void
    {
        $type     = 'abc';
        $strategy = 'xyz';

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();

        $activationStrategyPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $activationStrategyPluginManager->expects(self::never())
            ->method('has');
        $activationStrategyPluginManager->expects(self::never())
            ->method('get');

        $monologHandlerPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::once())
            ->method('get')
            ->with($type)
            ->willReturn($handler2);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive([MonologHandlerPluginManager::class], [ActivationStrategyPluginManager::class])
            ->willReturnCallback(
                static function (string $with) use ($monologHandlerPluginManager) {
                    if (MonologHandlerPluginManager::class === $with) {
                        return $monologHandlerPluginManager;
                    }

                    throw new ServiceNotFoundException();
                }
            );

        $factory = new FingersCrossedHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Could not load service %s', ActivationStrategyPluginManager::class));

        $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true], 'activationStrategy' => $strategy, 'bufferSize' => 42, 'bubble' => false, 'stopBuffering' => false, 'passthruLevel' => LogLevel::WARNING]);
    }

    /**
     * @throws Exception
     */
    public function testInvoceWithHandlerConfig8(): void
    {
        $type     = 'abc';
        $strategy = 'xyz';

        $handler2 = $this->getMockBuilder(ChromePHPHandler::class)
            ->disableOriginalConstructor()
            ->getMock();

        $activationStrategyPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $activationStrategyPluginManager->expects(self::once())
            ->method('has')
            ->with($strategy)
            ->willReturn(true);
        $activationStrategyPluginManager->expects(self::once())
            ->method('get')
            ->with($strategy)
            ->willThrowException(new ServiceNotFoundException());

        $monologHandlerPluginManager = $this->getMockBuilder(AbstractPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $monologHandlerPluginManager->expects(self::never())
            ->method('has');
        $monologHandlerPluginManager->expects(self::once())
            ->method('get')
            ->with($type)
            ->willReturn($handler2);

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive([MonologHandlerPluginManager::class], [ActivationStrategyPluginManager::class])
            ->willReturnOnConsecutiveCalls($monologHandlerPluginManager, $activationStrategyPluginManager);

        $factory = new FingersCrossedHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Could not load ActivationStrategy class');

        $factory($container, '', ['handler' => ['type' => $type, 'enabled' => true], 'activationStrategy' => $strategy, 'bufferSize' => 42, 'bubble' => false, 'stopBuffering' => false, 'passthruLevel' => LogLevel::WARNING]);
    }
}
