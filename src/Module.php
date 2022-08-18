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

use Laminas\ModuleManager\Feature\ConfigProviderInterface;
use Laminas\ModuleManager\Feature\DependencyIndicatorInterface;

final class Module implements ConfigProviderInterface, DependencyIndicatorInterface
{
    /**
     * Return default configuration for laminas-mvc applications.
     *
     * @return array<string, array<string, array<int|string, string>>>
     * @phpstan-return array{service_manager: array{aliases: array<string|class-string, class-string>, abstract_factories: array<int, class-string>, factories: array<class-string, class-string>}}
     */
    public function getConfig(): array
    {
        $provider = new ConfigProvider();

        return [
            'service_manager' => $provider->getDependencyConfig(),
        ];
    }

    /**
     * Expected to return an array of modules on which the current one depends on
     *
     * @return array<int, string>
     */
    public function getModuleDependencies(): array
    {
        return [
            'Mimmi20\MonologFactory',
            'Laminas\Log',
        ];
    }
}
