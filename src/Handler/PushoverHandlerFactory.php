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
use Monolog\Handler\PushoverHandler;
use Monolog\Logger;
use Psr\Log\LogLevel;

use function array_key_exists;
use function extension_loaded;
use function ini_get;
use function is_array;
use function sprintf;

/**
 * @phpstan-import-type Level from Logger
 * @phpstan-import-type LevelName from Logger
 */
final class PushoverHandlerFactory implements FactoryInterface
{
    use AddFormatterTrait;
    use AddProcessorTrait;

    /**
     * @param string                                              $requestedName
     * @param array<string, (string|int|bool|array<string>)>|null $options
     * @phpstan-param array{token?: string, users?: array<string>|string, title?: string, level?: (Level|LevelName|LogLevel::*), bubble?: bool, useSSL?: bool, highPriorityLevel?: (Level|LevelName|LogLevel::*), emergencyLevel?: (Level|LevelName|LogLevel::*), retry?: int, expire?: int, timeout?: float, writeTimeout?: float, persistent?: bool, chunkSize?: int}|null $options
     *
     * @throws ServiceNotFoundException   if unable to resolve the service
     * @throws ServiceNotCreatedException if an exception is raised when creating a service
     * @throws ContainerException         if any other error occurs
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): PushoverHandler
    {
        if (!extension_loaded('sockets')) {
            throw new ServiceNotCreatedException(
                sprintf('The sockets extension is needed to use the %s', PushoverHandler::class)
            );
        }

        if (!is_array($options)) {
            throw new ServiceNotCreatedException('Options must be an Array');
        }

        if (!array_key_exists('token', $options)) {
            throw new ServiceNotCreatedException('No token provided');
        }

        if (!array_key_exists('users', $options)) {
            throw new ServiceNotCreatedException('No users provided');
        }

        $token             = $options['token'];
        $users             = $options['users'];
        $title             = null;
        $level             = LogLevel::DEBUG;
        $bubble            = true;
        $useSSL            = true;
        $highPriorityLevel = LogLevel::CRITICAL;
        $emergencyLevel    = LogLevel::EMERGENCY;
        $retry             = 30;
        $expire            = 25200;
        $timeout           = (float) ini_get('default_socket_timeout');
        $writeTimeout      = (float) ini_get('default_socket_timeout');

        if (array_key_exists('title', $options)) {
            $title = $options['title'];
        }

        if (array_key_exists('level', $options)) {
            $level = $options['level'];
        }

        if (array_key_exists('bubble', $options)) {
            $bubble = $options['bubble'];
        }

        if (array_key_exists('useSSL', $options)) {
            $useSSL = $options['useSSL'];
        }

        if (array_key_exists('highPriorityLevel', $options)) {
            $highPriorityLevel = $options['highPriorityLevel'];
        }

        if (array_key_exists('emergencyLevel', $options)) {
            $emergencyLevel = $options['emergencyLevel'];
        }

        if (array_key_exists('retry', $options)) {
            $retry = $options['retry'];
        }

        if (array_key_exists('expire', $options)) {
            $expire = $options['expire'];
        }

        if (array_key_exists('timeout', $options)) {
            $timeout = $options['timeout'];
        }

        if (array_key_exists('writeTimeout', $options)) {
            $writeTimeout = $options['writeTimeout'];
        }

        $handler = new PushoverHandler(
            $token,
            $users,
            $title,
            $level,
            $bubble,
            $useSSL,
            $highPriorityLevel,
            $emergencyLevel,
            $retry,
            $expire
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
