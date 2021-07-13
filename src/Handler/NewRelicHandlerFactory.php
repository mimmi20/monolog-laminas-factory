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
use Monolog\Handler\NewRelicHandler;
use Monolog\Handler\ProcessableHandlerInterface;
use Psr\Log\LogLevel;

use function array_key_exists;
use function assert;
use function is_array;

final class NewRelicHandlerFactory implements FactoryInterface
{
    use AddFormatterTrait;
    use AddProcessorTrait;

    /**
     * @param string                                $requestedName
     * @param array<string, (string|int|bool)>|null $options
     * @phpstan-param array{level?: (string|LogLevel::*), bubble?: bool, appName?: string, explodeArrays?: bool, transactionName?: string}|null $options
     *
     * @throws ServiceNotFoundException   if unable to resolve the service
     * @throws ServiceNotCreatedException if an exception is raised when creating a service
     * @throws ContainerException         if any other error occurs
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): NewRelicHandler
    {
        $level           = LogLevel::DEBUG;
        $bubble          = true;
        $appName         = null;
        $explodeArrays   = false;
        $transactionName = null;

        if (is_array($options)) {
            if (array_key_exists('level', $options)) {
                $level = $options['level'];
            }

            if (array_key_exists('bubble', $options)) {
                $bubble = (bool) $options['bubble'];
            }

            if (array_key_exists('appName', $options)) {
                $appName = (string) $options['appName'];
            }

            if (array_key_exists('explodeArrays', $options)) {
                $explodeArrays = (bool) $options['explodeArrays'];
            }

            if (array_key_exists('transactionName', $options)) {
                $transactionName = (string) $options['transactionName'];
            }
        }

        $handler = new NewRelicHandler(
            $level,
            $bubble,
            $appName,
            $explodeArrays,
            $transactionName
        );

        assert($handler instanceof HandlerInterface);
        assert($handler instanceof FormattableHandlerInterface);
        assert($handler instanceof ProcessableHandlerInterface);

        $this->addFormatter($container, $handler, $options);
        $this->addProcessor($container, $handler, $options);

        return $handler;
    }
}
