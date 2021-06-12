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

namespace Mimmi20\LoggerFactory;

use Cascader\Cascader;
use Laminas\Log\Logger;
use Laminas\Log\LoggerInterface;

final class ConfigProvider
{
    /**
     * Return general-purpose laminas-navigation configuration.
     *
     * @return array<string, array<string, array<int|string, string>>>
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencyConfig(),
            'monolog_handlers' => $this->getMonologHandlerConfig(),
            'monolog_processors' => $this->getMonologProcessorConfig(),
            'monolog_formatters' => $this->getMonologFormatterConfig(),
            'monolog' => $this->getMonologConfig(),
        ];
    }

    /**
     * Return application-level dependency configuration.
     *
     * @return array<string, array<string, string>>
     */
    public function getDependencyConfig(): array
    {
        return [
            'aliases' => [
                LoggerInterface::class => Logger::class,
            ],
            'factories' => [
                Logger::class => LoggerFactory::class,
                Cascader::class => CascaderFactory::class,
                MonologPluginManager::class => MonologPluginManagerFactory::class,
                MonologHandlerPluginManager::class => MonologHandlerPluginManagerFactory::class,
                MonologProcessorPluginManager::class => MonologProcessorPluginManagerFactory::class,
                MonologFormatterPluginManager::class => MonologFormatterPluginManagerFactory::class,
            ],
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function getMonologHandlerConfig(): array
    {
        return [
            'abstract_factories' => [
                MonologHandlerAbstractFactory::class,
            ],
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function getMonologProcessorConfig(): array
    {
        return [
            'abstract_factories' => [
                MonologAbstractFactory::class,
            ],
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function getMonologFormatterConfig(): array
    {
        return [
            'abstract_factories' => [
                MonologAbstractFactory::class,
            ],
        ];
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function getMonologConfig(): array
    {
        return [
            'factories' => [
                \Monolog\Logger::class => MonologFactory::class,
            ],
        ];
    }
}
