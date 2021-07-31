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
use Mimmi20\LoggerFactory\Handler\SlackHandlerFactory;
use Mimmi20\LoggerFactory\MonologFormatterPluginManager;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\SlackHandler;
use Monolog\Logger;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionProperty;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

use function sprintf;

final class SlackHandlerFactoryTest extends TestCase
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

        $factory = new SlackHandlerFactory();

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

        $factory = new SlackHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No token provided');

        $factory($container, '', []);
    }

    /**
     * @throws Exception
     */
    public function testInvoceWithConfigWithoutChannel(): void
    {
        $token = 'token';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new SlackHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No channel provided');

        $factory($container, '', ['token' => $token]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     *
     * @requires extension openssl
     */
    public function testInvoceWithConfig(): void
    {
        $token   = 'token';
        $channel = 'channel';

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new SlackHandlerFactory();

        $handler = $factory($container, '', ['token' => $token, 'channel' => $channel]);

        self::assertInstanceOf(SlackHandler::class, $handler);
        self::assertSame($token, $handler->getToken());
        self::assertSame(Logger::DEBUG, $handler->getLevel());
        self::assertTrue($handler->getBubble());
        self::assertSame('ssl://slack.com:443', $handler->getConnectionString());
        self::assertSame(60.0, $handler->getTimeout());
        self::assertSame(60.0, $handler->getWritingTimeout());
        self::assertSame(60.0, $handler->getConnectionTimeout());
        //self::assertSame(0, $handler->getChunkSize());
        self::assertFalse($handler->isPersistent());

        $slackRecord = $handler->getSlackRecord();

        $ch = new ReflectionProperty($slackRecord, 'channel');
        $ch->setAccessible(true);

        self::assertSame($channel, $ch->getValue($slackRecord));

        $un = new ReflectionProperty($slackRecord, 'username');
        $un->setAccessible(true);

        self::assertNull($un->getValue($slackRecord));

        $ua = new ReflectionProperty($slackRecord, 'useAttachment');
        $ua->setAccessible(true);

        self::assertTrue($ua->getValue($slackRecord));

        $ui = new ReflectionProperty($slackRecord, 'userIcon');
        $ui->setAccessible(true);

        self::assertNull($ui->getValue($slackRecord));

        $usa = new ReflectionProperty($slackRecord, 'useShortAttachment');
        $usa->setAccessible(true);

        self::assertFalse($usa->getValue($slackRecord));

        $ice = new ReflectionProperty($slackRecord, 'includeContextAndExtra');
        $ice->setAccessible(true);

        self::assertFalse($ice->getValue($slackRecord));

        $ef = new ReflectionProperty($slackRecord, 'excludeFields');
        $ef->setAccessible(true);

        self::assertSame([], $ef->getValue($slackRecord));

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
     *
     * @requires extension openssl
     */
    public function testInvoceWithConfig2(): void
    {
        $token         = 'token';
        $channel       = 'channel';
        $userName      = 'user';
        $iconEmoji     = 'icon';
        $excludeFields = ['abc', 'xyz'];
        $timeout       = 42.0;
        $writeTimeout  = 120.0;
        $persistent    = true;
        $chunkSize     = 100;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new SlackHandlerFactory();

        $handler = $factory($container, '', ['token' => $token, 'channel' => $channel, 'userName' => $userName, 'useAttachment' => false, 'iconEmoji' => $iconEmoji, 'level' => LogLevel::ALERT, 'bubble' => false, 'useShortAttachment' => true, 'includeContextAndExtra' => true, 'excludeFields' => $excludeFields, 'timeout' => $timeout, 'writeTimeout' => $writeTimeout, 'persistent' => $persistent, 'chunkSize' => $chunkSize]);

        self::assertInstanceOf(SlackHandler::class, $handler);
        self::assertSame($token, $handler->getToken());
        self::assertSame(Logger::ALERT, $handler->getLevel());
        self::assertFalse($handler->getBubble());
        self::assertSame('ssl://slack.com:443', $handler->getConnectionString());
        self::assertSame($writeTimeout, $handler->getTimeout());
        self::assertSame($writeTimeout, $handler->getWritingTimeout());
        self::assertSame($timeout, $handler->getConnectionTimeout());
        self::assertSame($chunkSize, $handler->getChunkSize());
        self::assertTrue($handler->isPersistent());

        $slackRecord = $handler->getSlackRecord();

        $ch = new ReflectionProperty($slackRecord, 'channel');
        $ch->setAccessible(true);

        self::assertSame($channel, $ch->getValue($slackRecord));

        $un = new ReflectionProperty($slackRecord, 'username');
        $un->setAccessible(true);

        self::assertSame($userName, $un->getValue($slackRecord));

        $ua = new ReflectionProperty($slackRecord, 'useAttachment');
        $ua->setAccessible(true);

        self::assertFalse($ua->getValue($slackRecord));

        $ui = new ReflectionProperty($slackRecord, 'userIcon');
        $ui->setAccessible(true);

        self::assertSame($iconEmoji, $ui->getValue($slackRecord));

        $usa = new ReflectionProperty($slackRecord, 'useShortAttachment');
        $usa->setAccessible(true);

        self::assertTrue($usa->getValue($slackRecord));

        $ice = new ReflectionProperty($slackRecord, 'includeContextAndExtra');
        $ice->setAccessible(true);

        self::assertTrue($ice->getValue($slackRecord));

        $ef = new ReflectionProperty($slackRecord, 'excludeFields');
        $ef->setAccessible(true);

        self::assertSame($excludeFields, $ef->getValue($slackRecord));

        self::assertInstanceOf(LineFormatter::class, $handler->getFormatter());

        $proc = new ReflectionProperty($handler, 'processors');
        $proc->setAccessible(true);

        $processors = $proc->getValue($handler);

        self::assertIsArray($processors);
        self::assertCount(0, $processors);
    }

    /**
     * @throws Exception
     *
     * @requires extension openssl
     */
    public function testInvoceWithConfigAndBoolFormatter(): void
    {
        $token         = 'token';
        $channel       = 'channel';
        $userName      = 'user';
        $iconEmoji     = 'icon';
        $excludeFields = ['abc', 'xyz'];
        $timeout       = 42.0;
        $writeTimeout  = 120.0;
        $persistent    = true;
        $chunkSize     = 100;
        $formatter     = true;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new SlackHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class)
        );

        $factory($container, '', ['token' => $token, 'channel' => $channel, 'userName' => $userName, 'useAttachment' => false, 'iconEmoji' => $iconEmoji, 'level' => LogLevel::ALERT, 'bubble' => false, 'useShortAttachment' => true, 'includeContextAndExtra' => true, 'excludeFields' => $excludeFields, 'timeout' => $timeout, 'writeTimeout' => $writeTimeout, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     *
     * @requires extension openssl
     */
    public function testInvoceWithConfigAndFormatter(): void
    {
        $token         = 'token';
        $channel       = 'channel';
        $userName      = 'user';
        $iconEmoji     = 'icon';
        $excludeFields = ['abc', 'xyz'];
        $timeout       = 42.0;
        $writeTimeout  = 120.0;
        $persistent    = true;
        $chunkSize     = 100;
        $formatter     = $this->getMockBuilder(LineFormatter::class)
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

        $factory = new SlackHandlerFactory();

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not find service %s', MonologFormatterPluginManager::class)
        );

        $factory($container, '', ['token' => $token, 'channel' => $channel, 'userName' => $userName, 'useAttachment' => false, 'iconEmoji' => $iconEmoji, 'level' => LogLevel::ALERT, 'bubble' => false, 'useShortAttachment' => true, 'includeContextAndExtra' => true, 'excludeFields' => $excludeFields, 'timeout' => $timeout, 'writeTimeout' => $writeTimeout, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'formatter' => $formatter]);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidArgumentException
     *
     * @requires extension openssl
     */
    public function testInvoceWithConfigAndFormatter2(): void
    {
        $token         = 'token';
        $channel       = 'channel';
        $userName      = 'user';
        $iconEmoji     = 'icon';
        $excludeFields = ['abc', 'xyz'];
        $timeout       = 42.0;
        $writeTimeout  = 120.0;
        $persistent    = true;
        $chunkSize     = 100;
        $formatter     = $this->getMockBuilder(LineFormatter::class)
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

        $factory = new SlackHandlerFactory();

        $handler = $factory($container, '', ['token' => $token, 'channel' => $channel, 'userName' => $userName, 'useAttachment' => false, 'iconEmoji' => $iconEmoji, 'level' => LogLevel::ALERT, 'bubble' => false, 'useShortAttachment' => true, 'includeContextAndExtra' => true, 'excludeFields' => $excludeFields, 'timeout' => $timeout, 'writeTimeout' => $writeTimeout, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'formatter' => $formatter]);

        self::assertInstanceOf(SlackHandler::class, $handler);
        self::assertSame($token, $handler->getToken());
        self::assertSame(Logger::ALERT, $handler->getLevel());
        self::assertFalse($handler->getBubble());
        self::assertSame('ssl://slack.com:443', $handler->getConnectionString());
        self::assertSame($writeTimeout, $handler->getTimeout());
        self::assertSame($writeTimeout, $handler->getWritingTimeout());
        self::assertSame($timeout, $handler->getConnectionTimeout());
        self::assertSame($chunkSize, $handler->getChunkSize());
        self::assertTrue($handler->isPersistent());

        $slackRecord = $handler->getSlackRecord();

        $ch = new ReflectionProperty($slackRecord, 'channel');
        $ch->setAccessible(true);

        self::assertSame($channel, $ch->getValue($slackRecord));

        $un = new ReflectionProperty($slackRecord, 'username');
        $un->setAccessible(true);

        self::assertSame($userName, $un->getValue($slackRecord));

        $ua = new ReflectionProperty($slackRecord, 'useAttachment');
        $ua->setAccessible(true);

        self::assertFalse($ua->getValue($slackRecord));

        $ui = new ReflectionProperty($slackRecord, 'userIcon');
        $ui->setAccessible(true);

        self::assertSame($iconEmoji, $ui->getValue($slackRecord));

        $usa = new ReflectionProperty($slackRecord, 'useShortAttachment');
        $usa->setAccessible(true);

        self::assertTrue($usa->getValue($slackRecord));

        $ice = new ReflectionProperty($slackRecord, 'includeContextAndExtra');
        $ice->setAccessible(true);

        self::assertTrue($ice->getValue($slackRecord));

        $ef = new ReflectionProperty($slackRecord, 'excludeFields');
        $ef->setAccessible(true);

        self::assertSame($excludeFields, $ef->getValue($slackRecord));

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
     * @requires extension openssl
     */
    public function testInvoceWithConfigAndBoolProcessors(): void
    {
        $token         = 'token';
        $channel       = 'channel';
        $userName      = 'user';
        $iconEmoji     = 'icon';
        $excludeFields = ['abc', 'xyz'];
        $timeout       = 42.0;
        $writeTimeout  = 120.0;
        $persistent    = true;
        $chunkSize     = 100;
        $processors    = true;

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(self::never())
            ->method('has');
        $container->expects(self::never())
            ->method('get');

        $factory = new SlackHandlerFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Processors must be an Array');

        $factory($container, '', ['token' => $token, 'channel' => $channel, 'userName' => $userName, 'useAttachment' => false, 'iconEmoji' => $iconEmoji, 'level' => LogLevel::ALERT, 'bubble' => false, 'useShortAttachment' => true, 'includeContextAndExtra' => true, 'excludeFields' => $excludeFields, 'timeout' => $timeout, 'writeTimeout' => $writeTimeout, 'persistent' => $persistent, 'chunkSize' => $chunkSize, 'processors' => $processors]);
    }
}
