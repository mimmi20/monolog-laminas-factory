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
use Monolog\Handler\FormattableHandlerInterface;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\ProcessableHandlerInterface;
use Monolog\Handler\RollbarHandler;
use Psr\Log\LogLevel;
use Rollbar\Config;
use Rollbar\RollbarLogger;

use function array_key_exists;
use function assert;
use function is_array;

final class RollbarHandlerFactory implements FactoryInterface
{
    use AddFormatterTrait;
    use AddProcessorTrait;

    /**
     * @param string                                $requestedName
     * @param array<string, (string|int|bool)>|null $options
     * @phpstan-param array{access_token?: string, enabled?: bool, transmit?: bool, log_payload?: bool, verbose?: string, allow_exec?: bool, level?: (string|LogLevel::*), bubble?: bool, environment?: string, root?: string}|null $options
     *
     * @throws ServiceNotFoundException   if unable to resolve the service
     * @throws ServiceNotCreatedException if an exception is raised when creating a service
     * @throws ContainerException         if any other error occurs
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): RollbarHandler
    {
        if (!is_array($options)) {
            throw new ServiceNotCreatedException('Options must be an Array');
        }

        if (!array_key_exists('access_token', $options)) {
            throw new ServiceNotCreatedException('No access token provided');
        }

        $token      = $options['access_token'];
        $enabled    = true;
        $transmit   = true;
        $logPayload = true;
        $allowExec  = true;
        $verbose    = Config::VERBOSE_NONE;
        $level      = LogLevel::DEBUG;
        $bubble     = true;

        if (array_key_exists('enabled', $options)) {
            $enabled = $options['enabled'];
        }

        if (array_key_exists('transmit', $options)) {
            $transmit = $options['transmit'];
        }

        if (array_key_exists('log_payload', $options)) {
            $logPayload = $options['log_payload'];
        }

        if (array_key_exists('verbose', $options)) {
            $verbose = $options['verbose'];
        }

        if (array_key_exists('allow_exec', $options)) {
            $allowExec = $options['allow_exec'];
        }

        if (array_key_exists('level', $options)) {
            $level = $options['level'];
        }

        if (array_key_exists('bubble', $options)) {
            $bubble = $options['bubble'];
        }

        $rollbarConfig = [
            'access_token' => $token,
            'enabled' => $enabled,
            'transmit' => $transmit,
            'log_payload' => $logPayload,
            'verbose' => $verbose,
            'allow_exec' => $allowExec,
        ];

        if (array_key_exists('environment', $options)) {
            $rollbarConfig['environment'] = $options['environment'];
        }

        if (array_key_exists('root', $options)) {
            $rollbarConfig['root'] = $options['root'];
        }

        $handler = new RollbarHandler(
            new RollbarLogger($rollbarConfig),
            $level,
            $bubble
        );

        assert($handler instanceof HandlerInterface);
        assert($handler instanceof FormattableHandlerInterface);
        assert($handler instanceof ProcessableHandlerInterface);

        $this->addFormatter($container, $handler, $options);
        $this->addProcessor($container, $handler, $options);

        return $handler;
    }
}
