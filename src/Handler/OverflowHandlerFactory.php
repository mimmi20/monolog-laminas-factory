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
use Monolog\Handler\OverflowHandler;
use Monolog\Handler\ProcessableHandlerInterface;
use Monolog\Logger;
use Psr\Log\LogLevel;

use function array_key_exists;
use function assert;
use function is_array;

final class OverflowHandlerFactory implements FactoryInterface
{
    use AddFormatterTrait;
    use AddProcessorTrait;
    use GetHandlerTrait;

    /**
     * @param string                                $requestedName
     * @param array<string, (string|int|bool)>|null $options
     * @phpstan-param array{handler: array{type: string, enabled?: bool, options?: array<mixed>}, thresholdMap: array{debug?: int, info?: int, notice?: int, warning?: int, error?: int, critical?: int, alert?: int, emergency?: int}, level?: (string|LogLevel::*), bubble?: bool}|null $options
     *
     * @throws ServiceNotFoundException   if unable to resolve the service
     * @throws ServiceNotCreatedException if an exception is raised when creating a service
     * @throws ContainerException         if any other error occurs
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): OverflowHandler
    {
        if (!is_array($options)) {
            throw new ServiceNotCreatedException('Options must be an Array');
        }

        if (!array_key_exists('handler', $options)) {
            throw new ServiceNotCreatedException('No handler provided');
        }

        $handler = $this->getHandler($container, $options['handler']);

        if (null === $handler) {
            throw new ServiceNotCreatedException('forwarded handlers could not be disabled');
        }

        $thresholdMap = [
            Logger::DEBUG => $options['thresholdMap']['debug'] ?? 0,
            Logger::INFO => $options['thresholdMap']['info'] ?? 0,
            Logger::NOTICE => $options['thresholdMap']['notice'] ?? 0,
            Logger::WARNING => $options['thresholdMap']['warning'] ?? 0,
            Logger::ERROR => $options['thresholdMap']['error'] ?? 0,
            Logger::CRITICAL => $options['thresholdMap']['critical'] ?? 0,
            Logger::ALERT => $options['thresholdMap']['alert'] ?? 0,
            Logger::EMERGENCY => $options['thresholdMap']['emergency'] ?? 0,
        ];

        $level = LogLevel::DEBUG;

        if (array_key_exists('level', $options)) {
            $level = $options['level'];
        }

        $bubble = true;

        if (array_key_exists('bubble', $options)) {
            $bubble = (bool) $options['bubble'];
        }

        $handler = new OverflowHandler(
            $handler,
            $thresholdMap,
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
