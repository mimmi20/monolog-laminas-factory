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
use Monolog\Handler\LogglyHandler;
use Monolog\Handler\MissingExtensionException;
use Monolog\Logger;
use Psr\Log\LogLevel;

use function array_key_exists;
use function is_array;
use function sprintf;

/**
 * @phpstan-import-type Level from Logger
 * @phpstan-import-type LevelName from Logger
 */
final class LogglyHandlerFactory implements FactoryInterface
{
    use AddFormatterTrait;
    use AddProcessorTrait;

    /**
     * @param string                                $requestedName
     * @param array<string, (string|int|bool)>|null $options
     * @phpstan-param array{token?: string, level?: (Level|LevelName|LogLevel::*), bubble?: bool}|null $options
     *
     * @throws ServiceNotFoundException   if unable to resolve the service
     * @throws ServiceNotCreatedException if an exception is raised when creating a service
     * @throws ContainerException         if any other error occurs
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): LogglyHandler
    {
        if (!is_array($options)) {
            throw new ServiceNotCreatedException('Options must be an Array');
        }

        if (!array_key_exists('token', $options)) {
            throw new ServiceNotCreatedException('No token provided');
        }

        $token  = $options['token'];
        $level  = LogLevel::DEBUG;
        $bubble = true;

        if (array_key_exists('level', $options)) {
            $level = $options['level'];
        }

        if (array_key_exists('bubble', $options)) {
            $bubble = $options['bubble'];
        }

        try {
            $handler = new LogglyHandler(
                $token,
                $level,
                $bubble
            );
        } catch (MissingExtensionException $e) {
            throw new ServiceNotCreatedException(
                sprintf('Could not create %s', LogglyHandler::class),
                0,
                $e
            );
        }

        $this->addFormatter($container, $handler, $options);
        $this->addProcessor($container, $handler, $options);

        return $handler;
    }
}
