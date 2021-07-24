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
use Laminas\ServiceManager\Factory\FactoryInterface;
use Mimmi20\LoggerFactory\AddFormatterTrait;
use Mimmi20\LoggerFactory\AddProcessorTrait;
use Monolog\Handler\SocketHandler;
use Monolog\Logger;
use Psr\Log\LogLevel;

use function array_key_exists;
use function ini_get;
use function is_array;

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
     * @phpstan-param array{connectionString?: string, timeout?: float, writeTimeout?: float, level?: (Level|LevelName|LogLevel::*), bubble?: bool, persistent?: bool, chunkSize?: int}|null $options
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

        $connectionString = $options['connectionString'];
        $timeout          = (float) ini_get('default_socket_timeout');
        $writeTimeout     = (float) ini_get('default_socket_timeout');
        $level            = LogLevel::DEBUG;
        $bubble           = true;

        if (array_key_exists('timeout', $options)) {
            $timeout = $options['timeout'];
        }

        if (array_key_exists('writeTimeout', $options)) {
            $writeTimeout = $options['writeTimeout'];
        }

        if (array_key_exists('level', $options)) {
            $level = $options['level'];
        }

        if (array_key_exists('bubble', $options)) {
            $bubble = $options['bubble'];
        }

        $handler = new SocketHandler(
            $connectionString,
            $level,
            $bubble
        );

        if (!empty($timeout)) {
            $handler->setConnectionTimeout($timeout);
        }

        if (!empty($writeTimeout)) {
            $handler->setTimeout($writeTimeout);
            $handler->setWritingTimeout($writeTimeout);
        }

        if (array_key_exists('persistent', $options)) {
            $handler->setPersistent($options['persistent']);
        }

        if (array_key_exists('chunkSize', $options)) {
            $handler->setChunkSize($options['chunkSize']);
        }

        $this->addFormatter($container, $handler, $options);
        $this->addProcessor($container, $handler, $options);

        return $handler;
    }
}
