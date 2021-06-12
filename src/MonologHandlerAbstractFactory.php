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
use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\Factory\AbstractFactoryInterface;
use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\FormattableHandlerInterface;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\ProcessableHandlerInterface;
use Psr\Container\ContainerExceptionInterface;

use function array_key_exists;
use function array_reverse;
use function assert;
use function class_exists;
use function is_array;
use function is_callable;
use function sprintf;

final class MonologHandlerAbstractFactory implements AbstractFactoryInterface
{
    /**
     * Create an object
     *
     * @param string                                                                    $requestedName
     * @param array<int|string, array<string, array<string, mixed>|string>|string>|null $options
     * @phpstan-param array{parameters?: array, processors?: array{callable|array{enabled?: bool, name: string, parameters?: array{mixed}}}, formatter?: array{enabled?: bool, name: string, parameters?: array{mixed}}} $options
     *
     * @throws ServiceNotFoundException   if unable to resolve the service
     * @throws ServiceNotCreatedException if an exception is raised when creating a service
     * @throws ContainerException         if any other error occurs
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): HandlerInterface
    {
        try {
            $cascader = $container->get(Cascader::class);
        } catch (ContainerExceptionInterface $e) {
            throw new ServiceNotFoundException(sprintf('Could not find service %s', Cascader::class), 0, $e);
        }

        $handler = $cascader->create($requestedName, $options['parameters'] ?? []);

        assert($handler instanceof HandlerInterface);

        if (
            $handler instanceof ProcessableHandlerInterface
            && is_array($options)
            && array_key_exists('processors', $options)
            && is_array($options['processors'])
        ) {
            foreach (array_reverse($options['processors']) as $processorConfig) {
                $processor = $this->createProcessor($processorConfig, $container);

                if (null === $processor) {
                    continue;
                }

                $handler->pushProcessor($processor);
            }
        }

        if (
            $handler instanceof FormattableHandlerInterface
            && is_array($options)
            && array_key_exists('formatter', $options)
            && is_array($options['formatter'])
        ) {
            $formatter = $this->createFormatter($options['formatter'], $container);

            if (null !== $formatter) {
                $handler->setFormatter($formatter);
            }
        }

        return $handler;
    }

    /**
     * Can the factory create an instance for the service?
     *
     * @param string $requestedName
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    public function canCreate(ContainerInterface $container, $requestedName): bool
    {
        return class_exists($requestedName);
    }

    /**
     * @param array<string, array<string, mixed>|string>|callable $processorConfig
     * @phpstan-param callable|array{enabled?: bool, name: string, parameters?: array{mixed}} $processorConfig
     *
     * @throws ServiceNotFoundException   if unable to resolve the service
     * @throws ServiceNotCreatedException if an exception is raised when creating a service
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

    /**
     * @param array<string, array<string, mixed>|string> $formatterConfig
     * @phpstan-param array{enabled?: bool, name: string, parameters?: array{mixed}} $formatterConfig
     *
     * @throws ServiceNotFoundException   if unable to resolve the service
     * @throws ServiceNotCreatedException if an exception is raised when creating a service
     */
    private function createFormatter(array $formatterConfig, ContainerInterface $container): ?FormatterInterface
    {
        if (array_key_exists('enabled', $formatterConfig) && !$formatterConfig['enabled']) {
            return null;
        }

        if (!array_key_exists('name', $formatterConfig)) {
            throw new ServiceNotCreatedException('Options must contain a name for the formatter');
        }

        try {
            $formatter = $container->get(MonologFormatterPluginManager::class)->get(
                $formatterConfig['name'],
                $formatterConfig['parameters'] ?? []
            );
        } catch (ContainerExceptionInterface $e) {
            throw new ServiceNotFoundException(sprintf('Could not find service %s', MonologFormatterPluginManager::class), 0, $e);
        }

        assert($formatter instanceof FormatterInterface);

        return $formatter;
    }
}
