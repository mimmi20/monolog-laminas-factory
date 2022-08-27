<?php
/**
 * This file is part of the mimmi20/monolog-laminas-factory package.
 *
 * Copyright (c) 2021-2022, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20Test\LoggerFactory\Compare;

use Laminas\Log\Formatter\Simple;
use Laminas\Log\Logger;
use Laminas\Log\LoggerInterface;
use Laminas\Log\Processor\PsrPlaceholder;
use Laminas\Log\ProcessorPluginManager;
use Laminas\Log\Writer\Stream;
use Laminas\Log\WriterPluginManager;
use Laminas\Stdlib\SplPriorityQueue;
use Mimmi20\LoggerFactory\ConfigProvider;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\Exception;
use Psr\Container\ContainerExceptionInterface;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

final class LoggerFactoryTest extends AbstractTest
{
    protected function setUp(): void
    {
        parent::setUp();

        vfsStream::setup('log', null, ['error.log' => '']);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ContainerExceptionInterface
     *
     * @group 123
     */
    public function testStreamWriter(): void
    {
        $sm = $this->serviceManager;
        $sm->setAllowOverride(true);

        $configProvider = new ConfigProvider();

        $sm->setService(
            'config',
            [
                'dependencies' => $configProvider->getDependencyConfig(),
                'monolog_handlers' => $configProvider->getMonologHandlerConfig(),
                'monolog_processors' => $configProvider->getMonologProcessorConfig(),
                'monolog_formatters' => $configProvider->getMonologFormatterConfig(),
                'monolog' => $configProvider->getMonologConfig(),
                'log' => [
                    Logger::class => [
                        'writers' => [
                            'file' => [
                                'name' => Stream::class,
                                'options' => [
                                    'stream' => vfsStream::url('log/error.log'),
                                    'chmod' => 0777,
                                    'formatter' => [
                                        'name' => Simple::class,
                                    ],
                                ],
                            ],
                        ],
                        'processors' => [
                            'psr3' => [
                                'name' => PsrPlaceholder::class,
                            ],
                        ],
                    ],
                ],
            ],
        );

        $sm->setAllowOverride(false);

        $logger = $sm->get(LoggerInterface::class);

        self::assertInstanceOf(Logger::class, $logger);
        self::assertInstanceOf(SplPriorityQueue::class, $logger->getWriters());
        self::assertCount(2, $logger->getWriters());
        self::assertInstanceOf(SplPriorityQueue::class, $logger->getProcessors());
        self::assertCount(1, $logger->getProcessors());
        self::assertInstanceOf(WriterPluginManager::class, $logger->getWriterPluginManager());
        self::assertInstanceOf(ProcessorPluginManager::class, $logger->getProcessorPluginManager());
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ContainerExceptionInterface
     *
     * @group 123
     */
    public function testStreamHandler(): void
    {
        $sm = $this->serviceManager;
        $sm->setAllowOverride(true);

        $configProvider = new ConfigProvider();

        $sm->setService(
            'config',
            [
                'dependencies' => $configProvider->getDependencyConfig(),
                'monolog_handlers' => $configProvider->getMonologHandlerConfig(),
                'monolog_processors' => $configProvider->getMonologProcessorConfig(),
                'monolog_formatters' => $configProvider->getMonologFormatterConfig(),
                'monolog' => $configProvider->getMonologConfig(),
                'log' => [
                    Logger::class => [
                        'name' => 'test',
                        'handlers' => [
                            'file' => [
                                'type' => StreamHandler::class,
                                'options' => [
                                    'stream' => vfsStream::url('log/error.log'),
                                    'filePermission' => 0777,
                                    'formatter' => [
                                        'type' => LineFormatter::class,
                                        'options' => ['allowInlineLineBreaks' => true],
                                    ],
                                ],
                            ],
                        ],
                        'processors' => [
                            'psr3' => [
                                'name' => PsrPlaceholder::class,
                            ],
                        ],
                    ],
                ],
            ],
        );

        $sm->setAllowOverride(false);

        $logger = $sm->get(LoggerInterface::class);

        self::assertInstanceOf(Logger::class, $logger);
        self::assertInstanceOf(SplPriorityQueue::class, $logger->getWriters());
        self::assertCount(2, $logger->getWriters());
        self::assertInstanceOf(SplPriorityQueue::class, $logger->getProcessors());
        self::assertCount(1, $logger->getProcessors());
        self::assertInstanceOf(WriterPluginManager::class, $logger->getWriterPluginManager());
        self::assertInstanceOf(ProcessorPluginManager::class, $logger->getProcessorPluginManager());
    }
}
