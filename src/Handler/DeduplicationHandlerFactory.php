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
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Mimmi20\LoggerFactory\AddFormatterTrait;
use Mimmi20\LoggerFactory\AddProcessorTrait;
use Monolog\Handler\DeduplicationHandler;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;

use function array_key_exists;
use function is_array;

/**
 * @phpstan-import-type Level from Logger
 * @phpstan-import-type LevelName from Logger
 */
final class DeduplicationHandlerFactory implements FactoryInterface
{
    use AddFormatterTrait;
    use AddProcessorTrait;
    use GetHandlerTrait;

    /**
     * @param string                           $requestedName
     * @param array<string, (string|int)>|null $options
     * @phpstan-param array{handler?: bool|array{type?: string, enabled?: bool, options?: array<mixed>}, deduplicationStore?: string, deduplicationLevel?: (Level|LevelName|LogLevel::*), time?: int, bubble?: bool}|null $options
     *
     * @throws ServiceNotFoundException   if unable to resolve the service
     * @throws ServiceNotCreatedException if an exception is raised when creating a service
     * @throws ContainerException         if any other error occurs
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): DeduplicationHandler
    {
        if (!is_array($options)) {
            throw new ServiceNotCreatedException('Options must be an Array');
        }

        if (!array_key_exists('handler', $options)) {
            throw new ServiceNotCreatedException('No handler provided');
        }

        if (!is_array($options['handler'])) {
            throw new ServiceNotCreatedException('HandlerConfig must be an Array');
        }

        $handler = $this->getHandler($container, $options['handler']);

        if (!$handler instanceof HandlerInterface) {
            throw new ServiceNotCreatedException('No active handler specified');
        }

        $deduplicationStore = null;
        $deduplicationLevel = LogLevel::ERROR;
        $time               = 60;
        $bubble             = true;

        if (array_key_exists('deduplicationStore', $options)) {
            $deduplicationStore = $options['deduplicationStore'];
        }

        if (array_key_exists('deduplicationLevel', $options)) {
            $deduplicationLevel = $options['deduplicationLevel'];
        }

        if (array_key_exists('time', $options)) {
            $time = $options['time'];
        }

        if (array_key_exists('bubble', $options)) {
            $bubble = $options['bubble'];
        }

        $handler = new DeduplicationHandler(
            $handler,
            $deduplicationStore,
            $deduplicationLevel,
            $time,
            $bubble,
        );

        $this->addFormatter($container, $handler, $options);
        $this->addProcessor($container, $handler, $options);

        return $handler;
    }
}
