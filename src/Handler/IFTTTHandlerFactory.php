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
use Monolog\Handler\IFTTTHandler;
use Monolog\Handler\ProcessableHandlerInterface;
use Psr\Log\LogLevel;

use function array_key_exists;
use function assert;
use function is_array;

final class IFTTTHandlerFactory implements FactoryInterface
{
    use AddFormatterTrait;
    use AddProcessorTrait;

    /**
     * @param string                                $requestedName
     * @param array<string, (string|int|bool)>|null $options
     * @phpstan-param array{eventName?: string, secretKey?: string, level?: (string|LogLevel::*), bubble?: bool}|null $options
     *
     * @throws ServiceNotFoundException   if unable to resolve the service
     * @throws ServiceNotCreatedException if an exception is raised when creating a service
     * @throws ContainerException         if any other error occurs
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): IFTTTHandler
    {
        if (!is_array($options)) {
            throw new ServiceNotCreatedException('Options must be an Array');
        }

        if (!array_key_exists('eventName', $options)) {
            throw new ServiceNotCreatedException('No eventName provided');
        }

        if (!array_key_exists('secretKey', $options)) {
            throw new ServiceNotCreatedException('No secretKey provided');
        }

        $eventName = (string) $options['eventName'];
        $secretKey = (string) $options['secretKey'];

        $level = LogLevel::DEBUG;

        if (array_key_exists('level', $options)) {
            $level = $options['level'];
        }

        $bubble = true;

        if (array_key_exists('bubble', $options)) {
            $bubble = (bool) $options['bubble'];
        }

        $handler = new IFTTTHandler(
            $eventName,
            $secretKey,
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
