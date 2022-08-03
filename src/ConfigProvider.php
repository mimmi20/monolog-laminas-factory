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

namespace Mimmi20\LoggerFactory;

use Laminas\Log\Logger;
use Laminas\Log\LoggerInterface;

final class ConfigProvider
{
    /**
     * Return general-purpose laminas-navigation configuration.
     *
     * @return array<string, array<string, array<int|string, string>>>
     * @phpstan-return array{dependencies: array{aliases: array<string|class-string, class-string>, abstract_factories: array<int, class-string>, factories: array<class-string, class-string>}}
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencyConfig(),
        ];
    }

    /**
     * Return application-level dependency configuration.
     *
     * @return array<string, array<int|string, string>>
     * @phpstan-return array{aliases: array<string|class-string, class-string>, abstract_factories: array<int, class-string>, factories: array<class-string, class-string>}
     */
    public function getDependencyConfig(): array
    {
        return [
            'aliases' => [
                LoggerInterface::class => Logger::class,
            ],
            'abstract_factories' => [
                LoggerAbstractFactory::class,
            ],
            'factories' => [
                Logger::class => LoggerAbstractFactory::class,
            ],
        ];
    }
}
