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

use DateTimeZone;
use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\NullHandler;
use Monolog\Logger;
use Psr\Container\ContainerExceptionInterface;
use Throwable;

use function array_key_exists;
use function array_reverse;
use function assert;
use function is_array;
use function is_callable;
use function is_string;
use function sprintf;

/**
 * Factory for monolog instances.
 */
final class MonologFactory implements FactoryInterface
{
    /**
     * @param string                                                                         $requestedName
     * @param array<string, array<string, array<string, mixed>|string>|callable|string>|null $options
     * @phpstan-param array{name: string, timezone?: string|DateTimeZone, handlers?: array{array{enabled?: bool, name: string}}, processors?: callable|array{enabled?: bool, name: string, parameters?: array}} $options
     *
     * @throws ServiceNotFoundException   if unable to resolve the service
     * @throws ServiceNotCreatedException if an exception is raised when creating a service
     * @throws ContainerException         if any other error occurs
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): Logger
    {
        if (!is_array($options)) {
            throw new ServiceNotCreatedException('The name for the monolog logger is missing');
        }

        $monolog = new Logger($options['name']);
        $monolog->pushHandler(new NullHandler());

        if (array_key_exists('timezone', $options)) {
            if (is_string($options['timezone'])) {
                try {
                    $timezone = new DateTimeZone($options['timezone']);
                } catch (Throwable $e) {
                    throw new ServiceNotCreatedException('An invalid timezone was set', 0, $e);
                }
            } elseif ($options['timezone'] instanceof DateTimeZone) {
                $timezone = $options['timezone'];
            } else {
                throw new ServiceNotCreatedException('An invalid timezone was set');
            }

            $monolog->setTimezone($timezone);
        }

        if (
            array_key_exists('handlers', $options)
            && is_array($options['handlers'])
        ) {
            foreach ($options['handlers'] as $handlerArray) {
                if (array_key_exists('enabled', $handlerArray) && !$handlerArray['enabled']) {
                    continue;
                }

                if (!isset($handlerArray['name'])) {
                    throw new ServiceNotCreatedException('Options must contain a name for the handler');
                }

                try {
                    $handler = $container->get(MonologHandlerPluginManager::class)->get(
                        $handlerArray['name'],
                        $handlerArray
                    );
                } catch (ContainerExceptionInterface $e) {
                    throw new ServiceNotFoundException(sprintf('Could not find service %s', MonologHandlerPluginManager::class), 0, $e);
                }

                assert($handler instanceof HandlerInterface);

                $monolog->pushHandler($handler);
            }
        }

        if (
            array_key_exists('processors', $options)
            && is_array($options['processors'])
        ) {
            foreach (array_reverse($options['processors']) as $processorConfig) {
                $processor = $this->createProcessor($processorConfig, $container);

                if (null === $processor) {
                    continue;
                }

                $monolog->pushProcessor($processor);
            }
        }

        return $monolog;
    }

    /**
     * @param array<string, array<string, mixed>|string>|callable $processorConfig
     *
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     */
    private function createProcessor($processorConfig, ContainerInterface $container): ?callable
    {
        if (is_callable($processorConfig)) {
            return $processorConfig;
        }

        if (array_key_exists('enabled', $processorConfig) && !$processorConfig['enabled']) {
            return null;
        }

        if (!array_key_exists('name', $processorConfig)) {
            throw new ServiceNotCreatedException('Options must contain a name for the processor');
        }

        try {
            $processor = $container->get(MonologProcessorPluginManager::class)->get(
                $processorConfig['name'],
                $processorConfig['parameters'] ?? []
            );
        } catch (ContainerExceptionInterface $e) {
            throw new ServiceNotFoundException(sprintf('Could not find service %s', MonologProcessorPluginManager::class), 0, $e);
        }

        assert(is_callable($processor));

        return $processor;
    }
}
