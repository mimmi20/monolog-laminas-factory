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

namespace Mimmi20\LoggerFactory\Handler;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Mimmi20\LoggerFactory\AddFormatterTrait;
use Mimmi20\LoggerFactory\AddProcessorTrait;
use Mimmi20\LoggerFactory\MonologHandlerPluginManager;
use Monolog\Handler\HandlerInterface;
use Psr\Container\ContainerExceptionInterface;

use function array_key_exists;
use function assert;
use function sprintf;

trait GetHandlerTrait
{
    use AddFormatterTrait;
    use AddProcessorTrait;

    /**
     * @param array<string, array<mixed>|bool|string> $options
     * @phpstan-param array{type?: string, enabled?: bool, options?: array<mixed>} $options
     *
     * @throws ServiceNotFoundException   if unable to resolve the service
     * @throws ServiceNotCreatedException if an exception is raised when creating a service
     * @throws ContainerException         if any other error occurs
     */
    private function getHandler(ContainerInterface $container, array $options): ?HandlerInterface
    {
        if (!array_key_exists('type', $options)) {
            throw new ServiceNotCreatedException('Options must contain a type for the handler');
        }

        if (array_key_exists('enabled', $options) && !$options['enabled']) {
            return null;
        }

        try {
            $handler = $container->get(MonologHandlerPluginManager::class)->get($options['type'], $options['options'] ?? []);
        } catch (ContainerExceptionInterface $e) {
            throw new ServiceNotFoundException(
                sprintf('Could not load handler class %s', $options['type']),
                0,
                $e
            );
        }

        assert($handler instanceof HandlerInterface);

        $this->addFormatter($container, $handler, $options['options'] ?? []);
        $this->addProcessor($container, $handler, $options['options'] ?? []);

        return $handler;
    }
}
