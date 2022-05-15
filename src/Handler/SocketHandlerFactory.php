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

namespace Mimmi20\LoggerFactory\Handler;

use Interop\Container\Exception\ContainerException;
use InvalidArgumentException;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Mimmi20\LoggerFactory\AddFormatterTrait;
use Mimmi20\LoggerFactory\AddProcessorTrait;
use Monolog\Handler\SocketHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;

use function array_key_exists;
use function is_array;
use function sprintf;

/**
 * @phpstan-import-type Level from Logger
 * @phpstan-import-type LevelName from Logger
 */
final class SocketHandlerFactory implements FactoryInterface
{
    use AddFormatterTrait;
    use AddProcessorTrait;

    /**
     * @param string                                      $requestedName
     * @param array<string, (string|int|bool|float)>|null $options
     * @phpstan-param array{connectionString?: string, timeout?: float, writingTimeout?: float, writeTimeout?: float, connectionTimeout?: float, level?: (Level|LevelName|LogLevel::*), bubble?: bool, persistent?: bool, chunkSize?: int}|null $options
     *
     * @throws ServiceNotFoundException   if unable to resolve the service
     * @throws ServiceNotCreatedException if an exception is raised when creating a service
     * @throws ContainerException         if any other error occurs
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): SocketHandler
    {
        if (!is_array($options)) {
            throw new ServiceNotCreatedException('Options must be an Array');
        }

        if (!array_key_exists('connectionString', $options)) {
            throw new ServiceNotCreatedException('No connectionString provided');
        }

        $level             = $options['level'] ?? LogLevel::DEBUG;
        $bubble            = $options['bubble'] ?? true;
        $timeout           = $options['timeout'] ?? 0.0;
        $writingTimeout    = $options['writingTimeout'] ?? $options['writeTimeout'] ?? 10.0;
        $connectionTimeout = $options['connectionTimeout'] ?? null;
        $persistent        = $options['persistent'] ?? false;
        $chunkSize         = $options['chunkSize'] ?? null;

        try {
            $handler = new SocketHandler(
                $options['connectionString'],
                $level,
                $bubble,
                $persistent,
                $timeout,
                $writingTimeout,
                $connectionTimeout,
                $chunkSize
            );
        } catch (InvalidArgumentException $e) {
            throw new ServiceNotCreatedException(
                sprintf('Could not create %s', SocketHandler::class),
                0,
                $e
            );
        }

        $this->addFormatter($container, $handler, $options);
        $this->addProcessor($container, $handler, $options);

        return $handler;
    }
}
