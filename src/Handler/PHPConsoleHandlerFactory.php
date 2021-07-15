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
use Monolog\Handler\PHPConsoleHandler;
use Monolog\Handler\ProcessableHandlerInterface;
use PhpConsole\Connector;
use PhpConsole\Storage;
use Psr\Container\ContainerExceptionInterface;
use Psr\Log\LogLevel;
use RuntimeException;

use function array_key_exists;
use function assert;
use function is_array;
use function is_string;
use function sprintf;

final class PHPConsoleHandlerFactory implements FactoryInterface
{
    use AddFormatterTrait;
    use AddProcessorTrait;

    /**
     * @param string                                          $requestedName
     * @param array<string, (string|int|bool|Connector)>|null $options
     * @phpstan-param array{connector: (string|Connector), options?: array{enabled?: bool, classesPartialsTraceIgnore?: array<string>, debugTagsKeysInContext?: array<(int|string)>, useOwnErrorsHandler?: bool, useOwnExceptionsHandler?: bool, sourcesBasePath?: string, registerHelper?: bool, serverEncoding?: string, headersLimit?: int, password?: string, enableSslOnlyMode?: bool, ipMasks?: array<mixed>, enableEvalListener?: bool, dumperDetectCallbacks?: bool, dumperLevelLimit?: int, dumperItemsCountLimit?: int, dumperItemSizeLimit?: int, dumperDumpSizeLimit?: int, detectDumpTraceAndSource?: bool, dataStorage?: Storage}, level?: (string|LogLevel::*), bubble?: bool}|null $options
     *
     * @throws ServiceNotFoundException   if unable to resolve the service
     * @throws ServiceNotCreatedException if an exception is raised when creating a service
     * @throws ContainerException         if any other error occurs
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): PHPConsoleHandler
    {
        if (!is_array($options)) {
            throw new ServiceNotCreatedException('Options must be an Array');
        }

        if (!array_key_exists('connector', $options)) {
            throw new ServiceNotCreatedException('No Service name provided for the required connector class');
        }

        if ($options['connector'] instanceof Connector) {
            $connector = $options['connector'];
        } elseif (!is_string($options['connector'])) {
            throw new ServiceNotCreatedException('No Service name provided for the required connector class');
        } else {
            try {
                $connector = $container->get($options['connector']);
            } catch (ContainerExceptionInterface $e) {
                throw new ServiceNotFoundException('Could not load connector class', 0, $e);
            }
        }

        $consoleOptions = (array) ($options['options'] ?? []);

        $level = LogLevel::DEBUG;

        if (array_key_exists('level', $options)) {
            $level = $options['level'];
        }

        $bubble = true;

        if (array_key_exists('bubble', $options)) {
            $bubble = (bool) $options['bubble'];
        }

        try {
            $handler = new PHPConsoleHandler(
                $consoleOptions,
                $connector,
                $level,
                $bubble
            );
        } catch (RuntimeException $e) {
            throw new ServiceNotCreatedException(
                sprintf('Could not create %s', PHPConsoleHandler::class),
                0,
                $e
            );
        }

        assert($handler instanceof HandlerInterface);
        assert($handler instanceof FormattableHandlerInterface);
        assert($handler instanceof ProcessableHandlerInterface);

        $this->addFormatter($container, $handler, $options);
        $this->addProcessor($container, $handler, $options);

        return $handler;
    }
}